<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\ProblemsAndGoals;
use App\Models\ScopeOfWork;
use App\Models\ScopeOfWorkAdditionalService;
use App\Models\ServiceScopes;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


/**
 * @authenticated
 */
class ScopeOfWorkController extends Controller
{
    private $promptType = PromptType::SCOPE_OF_WORK;

    /**
     * Get Scope of work list
     *
     * @group Scope Of Work
     * @queryParam problemGoalId integer page number.
     */
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
        ]);

        $data = $this::getScopeOfWorks($validatedData['problemGoalId']);


        return response()->json([
            'data' => $data]);
    }

    public static function getScopeOfWorks($problemGoalId)
    {
        $scopeOfWorks = ScopeOfWork::latest()->where('problemGoalId', $problemGoalId)->whereNull('additionalServiceId')->get();
        $additionalServices = ScopeOfWorkAdditionalService::with(['serviceInfo'])->where('problemGoalId', $problemGoalId)->get();
        return [
            'scopeOfWorks' => $scopeOfWorks,
            'additionalServices' => $additionalServices,
        ];
    }


    /**
     * Create a new Scope Of Work
     *
     * @group Scope Of Work
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam title string required
     */

    public function addNew(Request $request)
    {
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
            'title' => 'required|string'
        ]);
        try {
            $problemGoalsObj = ProblemsAndGoals::findOrFail($validatedData['problemGoalId']);

            $scopeWork = new ScopeOfWork();
            $scopeWork->problemGoalID = $problemGoalsObj->id;
            $scopeWork->transcriptId = $problemGoalsObj->transcriptId;
            $scopeWork->title = $request->get("title");
            $scopeWork->save();
            return response()->json([
                'data' => $scopeWork
            ], 201);

        } catch (\Exception $exception) {
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }
    /**
     * Create a new Scope Of Work
     *
     * @group Scope Of Work
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam scopeOfWorks object[] required An array of additional services.
     * @bodyParam scopeOfWorks[].title int required The ID of the additional service. Example: 2
     * @bodyParam scopeOfWorks[].serial int required An array of deliverable IDs to be marked. Example: 1
     */

    public function addMulti(Request $request)
    {
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
            'scopeOfWorks' => 'required|array'
        ]);
        try {
            $problemGoalsObj = ProblemsAndGoals::findOrFail($validatedData['problemGoalId']);
            $batchId = (string) Str::uuid();

            $scopeWorkList = [];
            DB::beginTransaction();

            foreach ($validatedData['scopeOfWorks'] as $scope) {
                $scopeWork = new ScopeOfWork();
                $scopeWork->serial = $scope['serial'];
                $scopeWork->problemGoalID = $problemGoalsObj->id;
                $scopeWork->transcriptId = $problemGoalsObj->transcriptId;
                $scopeWork->serviceScopeId = null;
                $scopeWork->scopeText = null;
                $scopeWork->additionalServiceId = null;
                $scopeWork->title = $scope['title'];
                $scopeWork->batchId = $batchId;
                $scopeWork->save();
                $scopeWorkList[] = $scopeWork;
            }

            DB::commit();
            return response()->json([
                'data' => $scopeWorkList
            ], 201);

        } catch (\Exception $exception) {
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }


    /**
     * Generate Scope Of Work with AI
     *
     * @group Scope Of Work
     *
     * @bodyParam problemGoalID int required Id of the ProblemsAndGoals.
     */

    public function create(Request $request)
    {

        $prompt = PromptService::findPromptByType($this->promptType);
        if ($prompt == null) {
            $response = [
                'message' => 'Prompt not set for PromptType::MEETING_SUMMARY',
                'data' => []
            ];
            return response()->json($response, 422);
        }
        $validatedData = $request->validate([
            'problemGoalID' => 'required|int'
        ]);
        try {
            set_time_limit(500);


            $batchId = (string)Str::uuid();


            $findExisting = ScopeOfWork::where('problemGoalID', $validatedData['problemGoalID'])->first();

            if ($findExisting) {
                return WebApiResponse::error(500, $errors = [], 'The scope of work already generated.');
            }


            $problemGoalsObj = ProblemsAndGoals::with(['meetingTranscript', 'meetingTranscript.serviceInfo'])->findOrFail($validatedData['problemGoalID']);

            $serviceScope = ServiceScopes::where('projectTypeId', $problemGoalsObj->meetingTranscript->serviceInfo->projectTypeId)->get();


            DB::beginTransaction();
            $problemGoalsObj = ProblemsAndGoals::findOrFail($request->problemGoalID);

            $response = Http::timeout(450)->post(env('AI_APPLICATION_URL') . '/estimation/scope-of-works-generate', [
                'threadId' => $problemGoalsObj->meetingTranscript->threadId,
                'assistantId' => $problemGoalsObj->meetingTranscript->assistantId,
                'serviceId' => $problemGoalsObj->meetingTranscript->serviceId,
                'prompt' => $prompt->prompt,
            ]);

            if (!$response->successful()) {
                WebApiResponse::error(500, $errors = [], "Can't able to Scope of work, Please try again.");
            }
            $data = $response->json();
            Log::info(['Scope of work AI.', $data]);

            if (!is_array($data['data']['scopeOfWork']) || count($data['data']['scopeOfWork']) < 1 || !isset($data['data']['scopeOfWork'][0]['title'])) {
                return WebApiResponse::error(500, $errors = [], 'The scopes from AI is not expected output, Try again please');
            }
            $serviceScopeList = $serviceScope->map(function ($scope) {
                return [
                    'scopeId' => $scope->id,
                    'title' => strip_tags($scope->name),
                ];
            });
            $this->storeScopeOfWork(array_merge($serviceScopeList->toArray(), $data['data']['scopeOfWork']), $batchId, $problemGoalsObj, 1);
            DB::commit();

            $scopeOfWorks = ScopeOfWork::where('problemGoalID', $problemGoalsObj->id)->get();
            return response()->json([
                'data' => $scopeOfWorks
            ], 201);

        } catch (\Exception $exception) {
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }

    private function storeScopeOfWork($scopes, $batchId, $problemGoalsObj, $serial)
    {

        foreach ($scopes as $scope) {
            $scopeWork = new ScopeOfWork();
            $scopeWork->serial = $serial++;
            $scopeWork->problemGoalID = $problemGoalsObj->id;
            $scopeWork->transcriptId = $problemGoalsObj->transcriptId;
            $scopeWork->serviceScopeId = !empty($scope['scopeId']) ? $scope['scopeId'] : null;
            $scopeWork->scopeText = !empty($scope['details']) ? $scope['details'] : null;
            $scopeWork->additionalServiceId = !empty($scope['additionalServiceId']) ? $scope['additionalServiceId'] : null;
            $scopeWork->title = $scope['title'];
            $scopeWork->batchId = $batchId;
            $scopeWork->save();
        }
    }

    /**
     * Update select Scope Of Work
     *
     * @group Scope Of Work
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam scopeOfWorkIds string[] required An array of meeting links. Example: [1,2,3]
     * @bodyParam serviceIds int[] required An array of services. Example: [1,2,3]
     *
     */

    public function select(Request $request)
    {
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
            'scopeOfWorkIds' => 'required|array',
            'serviceIds' => 'present|array',
        ]);
        try {
            $problemGoalId = $validatedData['problemGoalId'];
            $scopeOfWorkIds = $validatedData['scopeOfWorkIds'];
            $serviceIds = $validatedData['serviceIds'];

            $problemGoalsObj = ProblemsAndGoals::findOrFail($validatedData['problemGoalId']);

            DB::beginTransaction();

            ScopeOfWork::where('problemGoalId', $problemGoalId)
                ->whereIn('id', $scopeOfWorkIds)
                ->whereNull('additionalServiceId')
                ->update(['isChecked' => 1]);

            // Update the records that should not be checked
            ScopeOfWork::where('problemGoalId', $problemGoalId)
                ->whereNotIn('id', $scopeOfWorkIds)
                ->whereNull('additionalServiceId')
                ->update(['isChecked' => 0]);

            $existingServiceIds = ScopeOfWorkAdditionalService::where('problemGoalId', $problemGoalId)
                ->pluck('selectedServiceId')
                ->toArray();

            // Determine service IDs to add and to delete
            $serviceIdsToAdd = array_diff($serviceIds, $existingServiceIds);
            $serviceIdsToDelete = array_diff($existingServiceIds, $serviceIds);

            $additionalServiceScopes = ServiceScopes::whereIn('serviceId', $serviceIdsToAdd)->get()->groupBy('serviceId');
            $batchId = (string)Str::uuid();

            foreach ($serviceIdsToAdd as $serviceIdValue) {
                if (!isset($additionalServiceScopes[$serviceIdValue])) {
                    continue;
                }
                $scopeOfWorkAdditionalService = new ScopeOfWorkAdditionalService();
                $scopeOfWorkAdditionalService->problemGoalId = $problemGoalId;
                $scopeOfWorkAdditionalService->transcriptId = $problemGoalsObj->transcriptId;
                $scopeOfWorkAdditionalService->selectedServiceId = $serviceIdValue;
                $scopeOfWorkAdditionalService->save();

                $this->storeScopeOfWork(
                    $additionalServiceScopes[$serviceIdValue]->map(function ($scope) {
                        return [
                            'scopeId' => $scope->id,
                            'title' => strip_tags($scope->name),
                            'additionalServiceId' => $scope->serviceId,
                        ];
                    })->toArray(),
                    $batchId,
                    $problemGoalsObj,
                    1
                );
            }

            ScopeOfWork::where('problemGoalId', $problemGoalId)->whereIn('additionalServiceId', $serviceIdsToDelete)->delete();

            ScopeOfWorkAdditionalService::where('problemGoalId', $problemGoalId)
                ->whereIn('selectedServiceId', $serviceIdsToDelete)
                ->delete();

            DB::commit();

            $response = [
                'message' => 'Scope of work selected successfully',
            ];
            return response()->json($response, 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }

    /**
     * Update Scope Of Work
     *
     * @group Scope Of Work
     *
     * @urlParam id int required Id of the ScopeOfWork.
     * @bodyParam title string required
     *
     */

    public function update($id, Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
        ]);

        $scopeOfWork = ScopeOfWork::findOrFail($id);
        $scopeOfWork->title = $request->title;
        $scopeOfWork->save();

        $response = [
            'message' => 'Scope of work updated successfully',
            'data' => $scopeOfWork,
        ];

        return response()->json($response, 201);
    }
}
