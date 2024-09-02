<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\Associate;
use App\Models\Deliberable;
use App\Models\DeliverablesNotes;
use App\Models\EstimationTask;
use App\Models\ProblemsAndGoals;
use App\Models\ProjectTeam;
use App\Models\Prompt;
use App\Models\ScopeOfWork;
use App\Models\ScopeOfWorkAdditionalService;
use App\Models\ServiceDeliverables;
use App\Models\ServiceDeliverableTasks;
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

class EstimationsTasksController extends Controller
{

    private $promptType = PromptType::TASKS;

    /**
     * Get Estimation Task list
     *
     * @group Estimation Task
     * @queryParam problemGoalId integer page number.
     */
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
        ]);

        $data = $this->getEstimationTasks($validatedData['problemGoalId']);
        // Fetch all data if no page number is provided
        return response()->json([
            'data'=> $data,
        ]);
    }

    public static function getEstimationTasks($problemGoalId){
        $problemAndGoalObj = ProblemsAndGoals::findOrFail($problemGoalId);
        $getEstimationTasks = EstimationTask::with(['associate','additionalServiceInfo','deliverable','deliverable.scopeOfWork','deliverable.scopeOfWork.additionalServiceInfo'])->latest('created_at')->where('problemGoalId',$problemGoalId)->get();
        $projectTeams = ProjectTeam::with(['employeeRoleInfo','associate'])->where('transcriptId',$problemAndGoalObj->transcriptId)->get();
        return [
            'tasks'=>$getEstimationTasks,
            'projectTeams'=> $projectTeams,
        ];
    }

    /**
     * Create a new Estimation Task
     *
     * @group Estimation Task
     *
     * @bodyParam deliverableId int required Id of the Deliverable
     * @bodyParam title string required
     * @bodyParam estimationTasksParentId int not required
     * @bodyParam estimateHours int not required
     * @bodyParam employeeRoleId int not required
     * @bodyParam userId int not required
     * @bodyParam serial int required . Example: 1
     */

    public function addNew(Request $request){
        $validatedData = $request->validate([
            'deliverableId' => 'required|int',
            'estimationTasksParentId' => 'int',
            'estimateHours' => 'int',
            'employeeRoleId' => 'int',
            'serial' => 'int',
            'userId' => 'int',
            'title' => 'required|string'
        ]);
        try{
            $deliverable = Deliberable::findOrFail($validatedData['deliverableId']);
            $problemAndGoal = ProblemsAndGoals::with(['meetingTranscript'])->where('id',$deliverable->problemGoalId)->firstOrFail();

            $estimationTask = new EstimationTask();
            $estimationTask->transcriptId = $problemAndGoal->meetingTranscript->id;
            $estimationTask->problemGoalId = $problemAndGoal->id;
            $estimationTask->deliverableId = $deliverable->id;
            $estimationTask->estimateHours = !empty($validatedData['estimateHours'])? $validatedData['estimateHours']: 0.00;
            $estimationTask->employeeRoleId = !empty($validatedData['employeeRoleId'])? $validatedData['employeeRoleId']: null;
            $estimationTask->userId = !empty($validatedData['userId'])? $validatedData['userId']: null;
            $estimationTask->estimationTasksParentId = !empty($validatedData['estimationTasksParentId'])? $validatedData['estimationTasksParentId']: null;
            $estimationTask->title = $validatedData['title'];
            $estimationTask->serial = $validatedData['serial'];
            $estimationTask->isChecked = 1;
            $estimationTask->save();

            return response()->json([
                'data'=> $this->getEstimationTasks($validatedData['deliverableId'])
            ], 201);

        }catch (\Exception $exception){
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }


    /**
     * Generate Estimation Task
     *
     * @group Estimation Task
     *
     * @bodyParam problemGoalId int required Id of the Problem Goal ID.
     * @bodyParam deliverableId int required Id of the deliverable.
     *
     */

    public function create(Request $request){

        set_time_limit(500);
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
            'deliverableId' => 'required|int',
        ]);
        Log::info(['Estimation Generate Request.', $validatedData['problemGoalId'], $validatedData['deliverableId']]);
        $additionalServiceIds = ScopeOfWorkAdditionalService::where('problemGoalId',$validatedData['problemGoalId'])->get()->pluck('selectedServiceId')->toArray();
        $problemAndGoal = ProblemsAndGoals::with(['meetingTranscript'])->where('id',$validatedData['problemGoalId'])->firstOrFail();
        $serviceDeliverableTasks = ServiceDeliverableTasks::whereIn('serviceId',array_merge([$problemAndGoal->meetingTranscript->serviceId], $additionalServiceIds))->get();
        $deliverables = Deliberable::where('problemGoalID',$validatedData['problemGoalId'])->where('isChecked',1)->get();
        $serial = EstimationTask::where('problemGoalId', $validatedData['problemGoalId'])->max('serial') ?? 1;

        $input = [
            "CLIENT-EMAIL" => $problemAndGoal->meetingTranscript->clientEmail,
            "CLIENT-COMPANY-NAME" => $problemAndGoal->meetingTranscript->company,
            "CLIENT-PHONE" => $problemAndGoal->meetingTranscript->clientPhone,
        ];


        $batchId = (string) Str::uuid();

        $deliverablesToFormat = $deliverables->filter(function ($item){
            return is_null($item->serviceDeliverablesId);
        })->map(function ($deliverable){
            return [
                'id'=> $deliverable->id,
                'title'=>$deliverable->deliverablesText,
                'deliverablesText'=>$deliverable->deliverablesText,
            ];
        });
        $deliverablesWithScope = $deliverables->filter(function ($item){
            return !is_null($item->serviceDeliverablesId);
        });

        $findDeliverable = Deliberable::where('problemGoalID', $validatedData['problemGoalId'])->where('id',$validatedData['deliverableId'])->first();
        if (!$findDeliverable) {
            return WebApiResponse::error(500, $errors = [], 'The deliverable not found.');
        }


        $prompts = Prompt::where('type',$this->promptType)->orderBy('id','ASC')->get();
        if(count($prompts) < 1){
            $response = [
                'message' => 'Prompt not set for PromptType::PROBLEMS_AND_GOALS',
                'data' => []
            ];
            return response()->json($response, 422);
        }

        $findExisting = EstimationTask::where('problemGoalID',$validatedData['problemGoalId'])->where('problemGoalId',$validatedData['deliverableId'])->first();

        if($findExisting){
            return WebApiResponse::error(500, $errors = [], 'The task already generated.');
        }

        DB::beginTransaction();

        $response = Http::timeout(450)->post(env('AI_APPLICATION_URL') . '/estimation/task-generate', [
            'threadId' => $problemAndGoal->meetingTranscript->threadId,
            'assistantId' => $problemAndGoal->meetingTranscript->assistantId,
            'problemAndGoalsId' => $problemAndGoal->id,
            'deliverableTitle' => $findDeliverable->title,
            'deliverablesDetails' => $findDeliverable->deliverablesText,
            'prompts' => $prompts->map(function ($item, $key) {
                return [
                    'prompt_text'=> $item->prompt,
                    'action_type'=> $item->action_type,
                ];
            })->toArray(),
        ]);

        if (!$response->successful()) {
            return WebApiResponse::error(500, $errors = [], "Can't able to Task, Please try again.");
        }
        $data = $response->json();
        Log::info(['Estimation Generate AI.', $data]);

        foreach ($data['data']['tasks'] as $task) {
            $estimationTask = new EstimationTask();
            $estimationTask->transcriptId = $problemAndGoal->meetingTranscript->id;
            $estimationTask->problemGoalId = $problemAndGoal->id;
            $estimationTask->deliverableId = $findDeliverable->id;
            $estimationTask->estimateHours = $task['estimated_hours'];
            $estimationTask->additionalServiceId = null;
            $estimationTask->serviceDeliverableTasksId = null;
            $estimationTask->serviceDeliverableTasksParentId = null;
            $estimationTask->title = $task['title'];
            $estimationTask->details = $task['details'];
            $estimationTask->isChecked = 1;
            $estimationTask->batchId =$batchId;
            $estimationTask->serial = $serial++;
            $estimationTask->save();
            if(isset($task['sub_tasks']) && is_array($task['sub_tasks'])){
                $subTaskSerial = 1;
                foreach ($task['sub_tasks'] as $subTask){
                    $estimationSubTask = new EstimationTask();
                    $estimationSubTask->deliverableId = $findDeliverable->id;
                    $estimationSubTask->transcriptId = $problemAndGoal->meetingTranscript->id;
                    $estimationSubTask->problemGoalId = $problemAndGoal->id;
                    $estimationSubTask->estimationTasksParentId = $estimationTask->id;
                    $estimationSubTask->title = $subTask['title'];
                    $estimationSubTask->details = $subTask['details'];
                    $estimationSubTask->estimateHours = $subTask['estimated_hours'];
                    $estimationSubTask->isChecked = 1;
                    $estimationSubTask->serial = $subTaskSerial++;
                    $estimationSubTask->batchId =$batchId;
                    $estimationSubTask->save();
                }

            }
        }



        /*$teams =  ProjectTeam::where('transcriptId',$problemAndGoal->meetingTranscript->id)->get()->keyBy('employeeRoleId');
        $serviceTaskByServiceDeliverableId = $serviceDeliverableTasks->groupBy('serviceDeliverableId');
        foreach ($deliverablesWithScope as $deliverable){
            if(empty($serviceTaskByServiceDeliverableId[$deliverable->serviceDeliverablesId])) { continue; };
            foreach ($serviceTaskByServiceDeliverableId[$deliverable->serviceDeliverablesId] as $task){
                $title = strip_tags($task->name);
                foreach ($input as $key => $value) {
                    $placeholder = "{" . $key . "}";
                    $title = str_replace($placeholder, $value, $title);
                }
                $estimationTask = new EstimationTask();
                $estimationTask->transcriptId = $problemAndGoal->meetingTranscript->id;
                $estimationTask->problemGoalId = $problemAndGoal->id;
                $estimationTask->serviceDeliverableTasksId = $task->id;
                $estimationTask->employeeRoleId = $task->employeeRoleId;
                $estimationTask->associateId = !empty($teams[$task->employeeRoleId])? $teams[$task->employeeRoleId]->associateId : null;
                $estimationTask->serviceDeliverableTasksParentId = $task->parentTaskId;
                $estimationTask->additionalServiceId = $deliverable->additionalServiceId;
                $estimationTask->title = $title;
                $estimationTask->details = $task->description;
                $estimationTask->estimateHours = $task->cost;
                $estimationTask->isChecked = 1;
                $estimationTask->batchId = $batchId;
                $estimationTask->serviceDeliverablesId = $task->serviceDeliverableId;
                $estimationTask->deliverableId = $deliverable->id;
                $estimationTask->save();

            }
        }*/
        /*$deliverables = EstimationTask::where('problemGoalId',$problemAndGoal->id)->whereNotNull('serviceDeliverableTasksId')->get();
        $deliverablesByTasksId = $deliverables->keyBy('serviceDeliverableTasksId');
        foreach ($deliverables->filter(function ($deliverables){ return !is_null($deliverables->serviceDeliverableTasksParentId);}) as $deliverable){
            $deliverable->estimationTasksParentId = $deliverablesByTasksId[$deliverable->serviceDeliverableTasksParentId]->id;
            $deliverable->save();
        }*/
        DB::commit();

        $deliverableList = EstimationTask::with(['associate','additionalServiceInfo','deliverable','deliverable.scopeOfWork','deliverable.scopeOfWork.additionalServiceInfo'])->latest('created_at')->where('problemGoalId', $request->get('problemGoalId'))->get();
        return response()->json([
            'data'=>$deliverableList
        ], 201);
    }

    /**
     * Update Estimation Task
     *
     * @group Estimation Task
     *
     * @urlParam id int required Id of the EstimationTask.
     * @bodyParam title string required
     *
     */

    public function update($id, Request $request){
        $validatedData = $request->validate([
            'title' => 'required|string'
        ]);

        $estimationTask = EstimationTask::with(['associate'])->findOrFail($id);
        $estimationTask->title = $validatedData['title'];
        $estimationTask->save();

        $response = [
            'message' => 'Estimation Task updated successfully',
            'data' => $estimationTask,
        ];

        return response()->json($response, 201);
    }


    /**
     * Add Associate to Estimation Task
     *
     * @group Estimation Task
     *
     * @urlParam id int required Id of the EstimationTask.
     * @bodyParam associateId int required
     *
     */

    public function addAssociate($id, Request $request){
        $validatedData = $request->validate([
            'associateId' => 'required|int|exists:associates,id',
        ]);

        $estimationTask = EstimationTask::findOrFail($id);
        $estimationTask->associateId = $validatedData['associateId'];
        $estimationTask->isManualAssociated = true;
        $estimationTask->save();
        $estimationTask->load(['associate','additionalServiceInfo','deliverable','deliverable.scopeOfWork']);

        $response = [
            'message' => 'Estimation Task association saved successfully',
            'data' => $estimationTask,
        ];

        return response()->json($response, 201);
    }

    /**
     * Add Associate to Estimation Task
     *
     * @group Estimation Task
     *
     * @urlParam id int required Id of the EstimationTask.
     * @bodyParam estimateHours int required
     *
     */

    public function addEstimateHours($id, Request $request){
        $validatedData = $request->validate([
            'estimateHours' => 'required|int',
        ]);

        $estimationTask = EstimationTask::findOrFail($id);
        $estimationTask->estimateHours = $validatedData['estimateHours'];
        $estimationTask->save();
        $estimationTask->load(['associate','additionalServiceInfo','deliverable','deliverable.scopeOfWork']);

        $response = [
            'message' => 'Estimate hours saved successfully',
            'data' => $estimationTask,
        ];

        return response()->json($response, 201);
    }

    /**
     * Checked Estimation Task
     *
     * @group Estimation Task
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam taskIds int[] required An array of meeting links. Example: [1,2,3]
     *
     */

    public function checked(Request $request){
        try{
            $validatedData = $request->validate([
                'problemGoalId' => 'required|int',
                'taskIds' => 'required|array',
            ]);
            $problemGoalId = $validatedData['problemGoalId'];
            $taskIds = $validatedData['taskIds'];


            DB::beginTransaction();
            EstimationTask::where('problemGoalId', $problemGoalId)
                ->whereIn('id', $taskIds)
                ->update(['isChecked' => 1]);


            DB::commit();

            $response = [
                'message' => 'Task successfully checked',
            ];
            return response()->json($response, 201);
        }catch (\Exception $exception){
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }

    /**
     * Un-Checked Estimation Task
     *
     * @group Estimation Task
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam taskIds int[] required An array of meeting links. Example: [1,2,3]
     *
     */

    public function unChecked(Request $request){
        try{
            $validatedData = $request->validate([
                'problemGoalId' => 'required|int',
                'taskIds' => 'required|array',
            ]);
            $problemGoalId = $validatedData['problemGoalId'];
            $taskIds = $validatedData['taskIds'];


            DB::beginTransaction();
            EstimationTask::where('problemGoalId', $problemGoalId)
                ->whereIn('id', $taskIds)
                ->update(['isChecked' => 0]);


            DB::commit();

            $response = [
                'message' => 'Task successfully un-checked',
            ];
            return response()->json($response, 201);
        }catch (\Exception $exception){
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }

    /**
     * Serial Update Estimation Task
     *
     * @group Estimation Task
     *
     * @urlParam id int required Id of the Estimation Task.
     * @bodyParam serial int required
     *
     */

    public function updateSerial($id, Request $request)
    {
        $validatedData = $request->validate([
            'serial' => 'required|int',
        ]);

        $estimationTask = EstimationTask::findOrFail($id);
        $estimationTask->serial = $request->serial;
        $estimationTask->save();

        $response = [
            'message' => 'Estimation task serial updated successfully',
            'data' => $estimationTask,
        ];

        return response()->json($response, 201);
    }
}

