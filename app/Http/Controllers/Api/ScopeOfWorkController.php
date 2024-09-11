<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\Phase;
use App\Models\ProblemsAndGoals;
use App\Models\Prompt;
use App\Models\ScopeOfWork;
use App\Models\ScopeOfWorkAdditionalService;
use App\Models\ServiceGroups;
use App\Models\ServiceScopes;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use App\Services\Utility;
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
        $scopeOfWorks = ScopeOfWork::with(['phaseInfo'])->orderBy('serial','ASC')->where('problemGoalId', $problemGoalId)->get();
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
     * @bodyParam scopeText string nullable. Example: lorem ipsum
     * @bodyParam serial int required . Example: 1
     */

    public function addNew(Request $request)
    {
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
            'title' => 'required|string',
            'scopeText' => 'nullable|string',
            'serial' => 'required|int',
        ]);
        try {
            $problemAndGoal = ProblemsAndGoals::with(['meetingTranscript'])->findOrFail($validatedData['problemGoalId']);

            $scopeWork = new ScopeOfWork();
            $scopeWork->problemGoalID = $problemAndGoal->id;
            $scopeWork->transcriptId = $problemAndGoal->transcriptId;
            $scopeWork->title = Utility::textTransformToClientInfo($problemAndGoal, $validatedData['title']);
            $scopeWork->scopeText = $validatedData['scopeText'];
            $scopeWork->serial = $validatedData['serial'];
            $scopeWork->isManual = 1;
            $scopeWork->save();
            return response()->json([
                'data' => $scopeWork
            ], 201);

        } catch (\Exception $exception) {
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }
    /**
     * Create multi Scope Of Work
     *
     * @group Scope Of Work
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam scopeOfWorks object[] required An array of additional services.
     * @bodyParam scopeOfWorks[].title string required. Example: Lorem
     * @bodyParam scopeOfWorks[].scopeText string nullable. Example: lorem ipsum
     * @bodyParam scopeOfWorks[].serial int required . Example: 1
     * @bodyParam scopeOfWorks[].serviceId int required. Example: 1
     */

    public function addMulti(Request $request)
    {
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
            'scopeOfWorks' => 'required|array'
        ]);
        try {
            $problemAndGoal = ProblemsAndGoals::with(['meetingTranscript'])->findOrFail($validatedData['problemGoalId']);
            $batchId = (string) Str::uuid();

            $scopeWorkList = [];

            DB::beginTransaction();

            foreach ($validatedData['scopeOfWorks'] as $scope) {

                $scopeWork = new ScopeOfWork();
                $scopeWork->serial = $scope['serial'];
                $scopeWork->problemGoalID = $problemAndGoal->id;
                $scopeWork->transcriptId = $problemAndGoal->transcriptId;
                $scopeWork->serviceScopeId = null;
                $scopeWork->scopeText = $scope['scopeText'];
                $scopeWork->additionalServiceId = null;
                $scopeWork->title = Utility::textTransformToClientInfo($problemAndGoal, $scope['title']);
                $scopeWork->batchId = $batchId;
                $scopeWork->isManual = 1;
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
     * @bodyParam phaseId int required Id of the Phase.
     */

    public function generate(Request $request)
    {

        $prompts = Prompt::where('type',$this->promptType)->orderBy('serial','ASC')->get();
        if(count($prompts) < 1){
            $response = [
                'message' => 'Prompt not set for PromptType::SCOPE_OF_WORK',
                'data' => []
            ];
            return response()->json($response, 422);
        }
        $validatedData = $request->validate([
            'problemGoalID' => 'required|int',
            'phaseId' => 'required|int'
        ]);
        try {
            set_time_limit(500);
            $phase = Phase::where('id',$validatedData['phaseId'])->first();
            if(!$phase){
                return WebApiResponse::error(500, $errors = [], 'The phase is not found.');
            }

            $batchId = (string)Str::uuid();


            $findExisting = ScopeOfWork::where('problemGoalID', $validatedData['problemGoalID'])->where('phaseId',$validatedData['phaseId'])->first();

            if ($findExisting) {
                return WebApiResponse::error(500, $errors = [], 'The scope of work already generated.');
            }

            $serial = 0;

            $problemGoalsObj = ProblemsAndGoals::with(['meetingTranscript', 'meetingTranscript.serviceInfo'])->findOrFail($validatedData['problemGoalID']);


            DB::beginTransaction();

            $response = Http::timeout(450)->post(env('AI_APPLICATION_URL') . '/estimation/scope-of-works-generate', [
                'threadId' => $problemGoalsObj->meetingTranscript->threadId,
                'assistantId' => $problemGoalsObj->meetingTranscript->assistantId,
                'problemAndGoalsId' => $problemGoalsObj->id,
                'phaseTitle' => $phase->title,
                'phaseDetails' => $phase->details,
                'prompts' => $prompts->map(function ($item, $key) {
                    return [
                        'prompt_text'=> $item->prompt,
                        'action_type'=> $item->action_type,
                    ];
                })->toArray(),
            ]);

            if (!$response->successful()) {
                return WebApiResponse::error(500, $errors = [], "Can't able to Scope of work, Please try again.");
            }
            $data = $response->json();
            Log::info(['Scope of work AI.', $data]);

            if (!is_array($data['data']['scopeOfWork']) || count($data['data']['scopeOfWork']) < 1 || !isset($data['data']['scopeOfWork'][0]['title'])) {
                return WebApiResponse::error(500, $errors = [], 'The scopes from AI is not expected output, Try again please');
            }

            $data['data']['scopeOfWork'] = array_map(function ($item) use($phase){
                return array_merge($item,['phaseId'=>$phase->id]);
            },$data['data']['scopeOfWork']);

            $this->storeScopeOfWork($data['data']['scopeOfWork'], $batchId, $problemGoalsObj, $serial);
            DB::commit();

            $scopeOfWorks = ScopeOfWork::with(['phaseInfo'])->orderBy('serial','ASC')->where('problemGoalID', $problemGoalsObj->id)->get();
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
            $scopeWork->serial = ++$serial;
            $scopeWork->problemGoalID = $problemGoalsObj->id;
            $scopeWork->transcriptId = $problemGoalsObj->transcriptId;
            $scopeWork->phaseId = !empty($scope['phaseId']) ? $scope['phaseId'] : null;
            $scopeWork->serviceScopeId = !empty($scope['scopeId']) ? $scope['scopeId'] : null;
            $scopeWork->scopeText = !empty($scope['details']) ? $scope['details'] : null;
            $scopeWork->additionalServiceId = !empty($scope['additionalServiceId']) ? $scope['additionalServiceId'] : null;
            $scopeWork->title = Utility::textTransformToClientInfo($problemGoalsObj, $scope['title']);
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

            $problemGoalsObj = ProblemsAndGoals::with(['meetingTranscript'])->findOrFail($validatedData['problemGoalId']);

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

                $serviceGroups = ServiceGroups::where('serviceId', $serviceIdValue)->get();
                $serviceGroupMapWithPhase = [];
                $serial = Phase::where('problemGoalID', $validatedData['problemGoalId'])->max('serial') ?? 0;

                foreach ($serviceGroups as $serviceGroup) {
                    $phase = new Phase();
                    $phase->serial = ++$serial;
                    $phase->problemGoalID = $problemGoalsObj->id;
                    $phase->transcriptId = $problemGoalsObj->transcriptId;
                    $phase->title = Utility::textTransformToClientInfo($problemGoalsObj,$serviceGroup->name);
                    $phase->details = null;
                    $phase->serviceGroupId = $serviceGroup->id;
                    $phase->additionalServiceId = $serviceIdValue;
                    $phase->batchId = $batchId;
                    $phase->save();
                    $serviceGroupMapWithPhase[(string) $serviceGroup->id] = $phase->id;

                }
                $this->storeScopeOfWork(
                    $additionalServiceScopes[$serviceIdValue]->map(function ($scope) use($serviceGroupMapWithPhase) {
                        return [
                            'scopeId' => $scope->id,
                            'title' => $scope->name,
                            'phaseId' => $serviceGroupMapWithPhase[(string) $scope->serviceGroupId],
                            'additionalServiceId' => $scope->serviceId,
                        ];
                    })->toArray(),
                    $batchId,
                    $problemGoalsObj,
                    0
                );
            }

            Phase::where('problemGoalID', $problemGoalId)->whereIn('additionalServiceId', $serviceIdsToDelete)->delete();
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

    /**
     * Serial Update Scope Of Work
     *
     * @group Scope Of Work
     *
     * @urlParam id int required Id of the ScopeOfWork.
     * @bodyParam serial int required
     *
     */

    public function updateSerial($id, Request $request)
    {
        $validatedData = $request->validate([
            'serial' => 'required|int',
        ]);

        $scopeOfWork = ScopeOfWork::findOrFail($id);
        $scopeOfWork->serial = $validatedData['serial'];
        $scopeOfWork->save();

        $response = [
            'message' => 'Scope of work serial updated successfully',
            'data' => $scopeOfWork,
        ];

        return response()->json($response, 201);
    }
}
