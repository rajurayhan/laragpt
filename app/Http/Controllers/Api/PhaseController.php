<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\Phase;
use App\Models\ProblemsAndGoals;
use App\Models\Prompt;
use App\Services\ModelOrderManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


/**
 * @authenticated
 */
class PhaseController extends Controller
{
    private $promptType = PromptType::PHASE;

    /**
     * Get Phase list
     *
     * @group Phase
     * @queryParam problemGoalId integer page number.
     */
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
        ]);

        $data = $this::getPhases($validatedData['problemGoalId']);


        return response()->json([
            'data' => $data
        ]);
    }

    public static function getPhases($problemGoalId): array
    {
        $phases = Phase::orderBy('serial','ASC')->where('problemGoalId', $problemGoalId)->get();
        return [
            'phases' => $phases,
        ];
    }


    /**
     * Create a Phase
     *
     * @group Phase
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam title string required
     * @bodyParam serial int required . Example: 1
     */

    public function addNew(Request $request)
    {
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
            'title' => 'required|string',
            'serial' => 'required|int',
        ]);
        try {
            $problemGoalsObj = ProblemsAndGoals::with(['meetingTranscript'])->findOrFail($validatedData['problemGoalId']);


            // $phase = new Phase();
            // $phase->problemGoalID = $problemGoalsObj->id;
            // $phase->transcriptId = $problemGoalsObj->transcriptId;
            // $phase->title = $validatedData['title'];
            // $phase->serial = $validatedData['serial'];
            // $phase->save();
            $orderManager = new ModelOrderManagerService(Phase::class);
            $phase = $orderManager->addOrUpdateItem(array_merge($validatedData, ['transcriptId'=> $problemGoalsObj->transcriptId]), null,'problemGoalId', $validatedData['problemGoalId']);
            return response()->json([
                'data' => $phase
            ], 201);

        } catch (\Exception $exception) {
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }
    /**
     * Create a Phase
     *
     * @group Phase
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam phase object[] required An array of additional services.
     * @bodyParam phases[].title int required. Example: 2
     * @bodyParam phases[].serial int required . Example: 1
     */

    public function addMulti(Request $request)
    {
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
            'phases' => 'required|array'
        ]);
        try {
            $problemGoalsObj = ProblemsAndGoals::with(['meetingTranscript'])->findOrFail($validatedData['problemGoalId']);
            $batchId = (string) Str::uuid();

            $orderManager = new ModelOrderManagerService(Phase::class);
            DB::beginTransaction();
            $phases = [];
            foreach ($validatedData['phases'] as $phase) {
                // $title = strip_tags($phase['title']);
                // $phaseData = new Phase();
                // $phaseData->serial = $phase['serial'];
                // $phaseData->problemGoalID = $problemGoalsObj->id;
                // $phaseData->transcriptId = $problemGoalsObj->transcriptId;
                // $phaseData->title = $title;
                // $phaseData->batchId = $batchId;
                // $phaseData->save();
                $phaseData = $orderManager->addOrUpdateItem(array_merge($phase,
                    [
                        'transcriptId'=> $problemGoalsObj->transcriptId,
                        'problemGoalID' => $problemGoalsObj->id,
                        'title' => strip_tags($phase['title']),
                    ]), null,'problemGoalId', $validatedData['problemGoalId']);
                $phases[] = $phaseData;
            }

            DB::commit();
            return response()->json([
                'data' => $phases
            ], 201);

        } catch (\Exception $exception) {
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }


    /**
     * Generate phases with AI
     *
     * @group Phase
     *
     * @bodyParam problemGoalID int required Id of the ProblemsAndGoals.
     */

    public function create(Request $request)
    {

        $prompts = Prompt::where('type',$this->promptType)->orderBy('id','ASC')->get();
        if(count($prompts) < 1){
            $response = [
                'message' => 'Prompt not set for PromptType::PHASE',
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
            $findExisting = Phase::where('problemGoalID', $validatedData['problemGoalID'])->first();
            if ($findExisting) {
                return WebApiResponse::error(500, $errors = [], 'The phase of work already generated.');
            }
            $problemGoalsObj = ProblemsAndGoals::with(['meetingTranscript', 'meetingTranscript.serviceInfo'])->findOrFail($validatedData['problemGoalID']);

            DB::beginTransaction();

            $response = Http::timeout(450)->post(env('AI_APPLICATION_URL') . '/estimation/phase-generate', [
                'threadId' => $problemGoalsObj->meetingTranscript->threadId,
                'assistantId' => $problemGoalsObj->meetingTranscript->assistantId,
                'serviceId' => $problemGoalsObj->meetingTranscript->serviceId,
                'prompts' => $prompts->map(function ($item, $key) {
                    return [
                        'prompt_text'=> $item->prompt,
                        'action_type'=> $item->action_type,
                    ];
                })->toArray(),
            ]);

            if (!$response->successful()) {
                WebApiResponse::error(500, $errors = [], "Can't able to generate phase, Please try again.");
            }
            $data = $response->json();
            Log::info(['Phases AI.', $data]);

            if (!is_array($data['data']['phases']) || count($data['data']['phases']) < 1 || !isset($data['data']['phases'][0]['title'])) {
                return WebApiResponse::error(500, $errors = [], 'The phases from AI is not expected output, Try again please');
            }


            $this->storePhase($data['data']['phases'], $batchId, $problemGoalsObj, 1);
            DB::commit();

            $phases = Phase::orderBy('serial','ASC')->where('problemGoalID', $problemGoalsObj->id)->get();
            return response()->json([
                'data' => $phases
            ], 201);

        } catch (\Exception $exception) {
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }

    private function storePhase($phases, $batchId, $problemGoalsObj, $serial)
    {

        foreach ($phases as $phase) {
            $phaseData = new Phase();
            $phaseData->serial = $serial++;
            $phaseData->problemGoalID = $problemGoalsObj->id;
            $phaseData->transcriptId = $problemGoalsObj->transcriptId;
            $phaseData->title = $phase['title'];
            $phaseData->details = !empty($phase['details']) ? $phase['details'] : null;
            $phaseData->batchId = $batchId;
            $phaseData->save();
        }
    }

    /**
     * Update select phase
     *
     * @group Phase
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam phaseIds string[] required An array of meeting links. Example: [1,2,3]
     *
     */

    public function select(Request $request)
    {
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
            'phaseIds' => 'required|array',
        ]);
        try {
            $problemGoalId = $validatedData['problemGoalId'];
            $phaseIds = $validatedData['phaseIds'];

            DB::beginTransaction();

            Phase::where('problemGoalId', $problemGoalId)
                ->whereIn('id', $phaseIds)
                ->update(['isChecked' => 1]);

            // Update the records that should not be checked
            Phase::where('problemGoalId', $problemGoalId)
                ->whereNotIn('id', $phaseIds)
                ->update(['isChecked' => 0]);
            DB::commit();

            $response = [
                'message' => 'phases selected successfully',
            ];
            return response()->json($response, 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }

    /**
     * Update phase
     *
     * @group Phase
     *
     * @urlParam id int required Id of the Phase.
     * @bodyParam title string required
     *
     */

    public function update($id, Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
        ]);

        $phase = Phase::findOrFail($id);
        $phase->title = $request->title;
        $phase->save();

        $response = [
            'message' => 'Phase updated successfully',
            'data' => $phase,
        ];

        return response()->json($response, 201);
    }

    /**
     * Serial phase
     *
     * @group Phase
     *
     * @urlParam id int required Id of the Phase.
     * @bodyParam serial int required
     *
     */

    public function updateSerial($id, Request $request)
    {
        $validatedData = $request->validate([
            'serial' => 'required|int',
        ]);

        $phase = Phase::findOrFail($id);
        $phase->serial = $request->serial;
        $phase->save();

        $response = [
            'message' => 'Phase serial updated successfully',
            'data' => $phase,
        ];

        return response()->json($response, 201);
    }
}
