<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\Deliberable;
use App\Models\DeliverablesNotes;
use App\Models\ProblemsAndGoals;
use App\Models\Prompt;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\ScopeOfWork;
use App\Models\ServiceDeliverables;
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

class DeliverablesController extends Controller
{

    private $promptType = PromptType::DELIVERABLES;

    /**
     * Get Deliverable list
     *
     * @group Deliverable
     * @queryParam problemGoalId integer page number.
     */
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
        ]);

        $data = $this->getDeliverables($validatedData['problemGoalId']);
        // Fetch all data if no page number is provided
        return response()->json([
            'data'=> $data,
        ]);
    }

    public static function getDeliverables($problemGoalId){
        $deliverables = Deliberable::with(['scopeOfWork','additionalServiceInfo'])->latest()->where('problemGoalId',$problemGoalId)->get();
        $deliverableNotes = DeliverablesNotes::where('problemGoalId',$problemGoalId)->get();
        $questionAnswers = QuestionAnswer::with(['questionInfo'])->where('problemGoalId',$problemGoalId)->get();

        return [
            'deliverables'=> $deliverables,
            'deliverableNotes'=> $deliverableNotes,
            'questionAnswers'=> $questionAnswers,
        ];
    }

    /**
     * Create a new Deliverable
     *
     * @group Deliverable
     *
     * @bodyParam scopeOfWorkId int required Id of the Scope Of Work.
     * @bodyParam title string required
     */

    public function addNew(Request $request){
        $validatedData = $request->validate([
            'scopeOfWorkId' => 'required|int',
            'title' => 'required|string'
        ]);
        try{

            $scopeWork = ScopeOfWork::findOrFail($validatedData['scopeOfWorkId']);

            $deliverable = new Deliberable();
            $deliverable->scopeOfWorkId = $scopeWork->id;
            $deliverable->deliverablesText = null;
            $deliverable->transcriptId = $scopeWork->transcriptId;
            $deliverable->serviceScopeId = $scopeWork->serviceScopeId;
            $deliverable->problemGoalId = $scopeWork->problemGoalID;
            $deliverable->title = $validatedData['title'];
            $deliverable->isChecked = 1;
            $deliverable->save();
            return response()->json([
                'data'=>$scopeWork
            ], 201);

        }catch (\Exception $exception){
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }


    /**
     * Generate Deliverable
     *
     * @group Deliverable
     *
     * @bodyParam problemGoalId int required Id of the Problem Goal ID.
     *
     */

    public function create(Request $request){
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int'
        ]);
        set_time_limit(500);
        $prompts = Prompt::where('type',$this->promptType)->orderBy('id','ASC')->get();
        if(count($prompts) < 1){
            $response = [
                'message' => 'Prompt not set for PromptType::PROBLEMS_AND_GOALS',
                'data' => []
            ];
            return response()->json($response, 422);
        }

        $findExisting = Deliberable::where('problemGoalID',$validatedData['problemGoalId'])->first();

        if($findExisting){
            return WebApiResponse::error(500, $errors = [], 'The deliverable already generated.');
        }


        $problemAndGoal = ProblemsAndGoals::with(['meetingTranscript'])->where('id',$validatedData['problemGoalId'])->first();
        if(!$problemAndGoal){
            return WebApiResponse::error(500, $errors = [], 'Problem and Goal not found.');
        }
        $input = [
            "CLIENT-EMAIL" => $problemAndGoal->meetingTranscript->clientEmail,
            "CLIENT-COMPANY-NAME" => $problemAndGoal->meetingTranscript->company,
            "CLIENT-PHONE" => $problemAndGoal->meetingTranscript->clientPhone,
        ];
        $scopeOfWorks = ScopeOfWork::with(['meetingTranscript','deliverables'])
            ->where('problemGoalID', $validatedData['problemGoalId'])->where('isChecked', 1)
            ->get();
        if(count($scopeOfWorks)<1){
            return WebApiResponse::error(400, $errors = [], 'Scope of works not available.');
        }

        $serviceScopeList = $scopeOfWorks->filter(function ($value) {
            return !empty($value->serviceScopeId);
        });

        $scopeDeliveryList = $serviceScopeList->reduce(function ($carry, $item) use($input) {
            return $carry->merge($item->deliverables->map(function ($delivery) use($item, $input){
                $title = strip_tags($delivery->name);
                foreach ($input as $key => $value) {
                    $placeholder = "{" . $key . "}";
                    $title = str_replace($placeholder, $value, $title);
                }
                unset($item->deliverables);
                $delivery->scopeOfWork = $item;
                $delivery->name = $title;
                return $delivery;
            }));
        },collect([]));


        $scopeOfWorksKeyById = $scopeOfWorks->keyBy('id');


        $response = Http::timeout(450)->post(env('AI_APPLICATION_URL') . '/estimation/deliverables-generate', [
            'threadId' => $problemAndGoal->meetingTranscript->threadId,
            'assistantId' => $problemAndGoal->meetingTranscript->assistantId,
            'problemAndGoalsId' => $problemAndGoal->id,
            'prompts' => $prompts->pluck('prompt'),
        ]);

        if (!$response->successful()) {
            WebApiResponse::error(500, $errors = [], "Can't able to Scope of work, Please try again.");
        }
        $data = $response->json();
        Log::info(['Deliverables Generate AI.', $data]);

        if (!is_array($data['data']['deliverables']) || count($data['data']['deliverables']) < 1 || !isset($data['data']['deliverables'][0]['scopeOfWorkId'])) {
            return WebApiResponse::error(500, $errors = [], 'The deliverables from AI is not expected output, Try again please');
        }
        $deliverables = $data['data']['deliverables'];


        DB::beginTransaction();
        $batchId = (string) Str::uuid();
        foreach($deliverables as $deliverable){
            $scopeOfWork = $scopeOfWorksKeyById[$deliverable['scopeOfWorkId']];
            $deliverableObj = new Deliberable();
            $deliverableObj->scopeOfWorkId = $scopeOfWork->id;
            $deliverableObj->transcriptId = $scopeOfWork->transcriptId;
            $deliverableObj->serviceScopeId = $scopeOfWork->serviceScopeId;
            $deliverableObj->problemGoalId = $scopeOfWork->problemGoalID;
            $deliverableObj->title = $deliverable['title'];
            $deliverableObj->deliverablesText = $deliverable['details'];
            $deliverableObj->isChecked = 1;
            $deliverableObj->batchId = $batchId;
            $deliverableObj->save();
        }
        foreach($scopeDeliveryList as $deliverable){
            $deliverableObj = new Deliberable();
            $deliverableObj->serviceDeliverablesId = $deliverable->id;
            $deliverableObj->additionalServiceId = $deliverable->scopeOfWork->additionalServiceId;
            $deliverableObj->scopeOfWorkId = $deliverable->scopeOfWork->id;
            $deliverableObj->transcriptId = $deliverable->scopeOfWork->meetingTranscript->id;
            $deliverableObj->serviceScopeId = $deliverable->serviceScopeId;
            $deliverableObj->problemGoalId = $problemAndGoal->id;
            $deliverableObj->title = $deliverable->name;
            $deliverableObj->deliverablesText = null;
            $deliverableObj->isChecked = 1;
            $deliverableObj->batchId = $batchId;
            $deliverableObj->save();
        }
        DB::commit();

        $deliverableList = Deliberable::with(['scopeOfWork','additionalServiceInfo'])->latest()->where('problemGoalId', $request->get('problemGoalId'))->get();
        return response()->json([
            'data'=>$deliverableList
        ], 201);
    }

    /**
     * Select Deliverable
     *
     * @group Deliverable
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam deliverableIds int[] required An array of meeting links. Example: [1,2,3]
     * @bodyParam notes object[] required An array of notes details.
     * @bodyParam notes[].noteLink string required. Example: https://tldv.io/app/meetings/663e283b70cff500132a9bbd
     * @bodyParam notes[].note string required. Example: lorem ipsum
     * @bodyParam questions object[] required An array of notes details.
     * @bodyParam questions[].questionId Int required. Example: 1
     * @bodyParam questions[].answer string required. Example: lorem ipsum
     *
     */

    public function select(Request $request){
        try{
            $validatedData = $request->validate([
                'problemGoalId' => 'required|int',
                'deliverableIds' => 'required|array',
                'notes' => 'present|array',
                'notes.*.note' => 'required|string',
                'notes.*.noteLink' => 'required|url',
                'questions' => 'present|array',
                'questions.*.questionId' => 'required|int|exists:questions,id',
                'questions.*.answer' => 'required|string',
            ]);
            $problemGoalId = $validatedData['problemGoalId'];
            $deliverableIds = $validatedData['deliverableIds'];
            $notes = $validatedData['notes'];
            $questions = $validatedData['questions'];
            $problemAndGoal = ProblemsAndGoals::with(['meetingTranscript'])->findOrFail($problemGoalId);

            DB::beginTransaction();
            Deliberable::where('problemGoalId', $problemGoalId)
                ->whereIn('id', $deliverableIds)
                ->update(['isChecked' => 1]);

            // Update the records that should not be checked
            Deliberable::where('problemGoalId', $problemGoalId)
                ->whereNotIn('id', $deliverableIds)
                ->update(['isChecked' => 0]);

            DeliverablesNotes::where('problemGoalId', $problemGoalId)->delete();

            foreach ($notes as $note){
                $deliverablesNotes = new DeliverablesNotes();
                $deliverablesNotes->transcriptId = $problemAndGoal->transcriptId;
                $deliverablesNotes->problemGoalId = $problemAndGoal->id;
                $deliverablesNotes->note = $note['note'];
                $deliverablesNotes->noteLink = $note['noteLink'];
                $deliverablesNotes->save();
            }
            QuestionAnswer::where('problemGoalId', $problemGoalId)->delete();
            if(is_array($questions) && count($questions) > 0){
                $questionIds = array_map(function($question){
                    return $question['questionId'];
                },$questions);
                $questionsData = Question::whereIn('id', $questionIds)->get()->keyBy('id');
                foreach ($questions as $question){
                    if(empty($questionsData[$question['questionId']])) continue;
                    $deliverablesNotes = new QuestionAnswer();
                    $deliverablesNotes->title = $questionsData[$question['questionId']]->title;
                    $deliverablesNotes->answer = $question['answer'];
                    $deliverablesNotes->problemGoalId = $problemAndGoal->id;
                    $deliverablesNotes->transcriptId = $problemAndGoal->transcriptId;
                    $deliverablesNotes->questionId = $question['questionId'];
                    $deliverablesNotes->save();
                }
            }


            DB::commit();

            $response = [
                'message' => 'Deliverable selected successfully',
            ];
            return response()->json($response, 201);
        }catch (\Exception $exception){
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }

    /**
     * @group Deliverable
     * Select additional deliverables for a problem goal.
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals. Example: 1
     * @bodyParam additionalService object[] required An array of additional services.
     * @bodyParam additionalService[].additionalServiceId int required The ID of the additional service. Example: 2
     * @bodyParam additionalService[].deliverableIds int[] required An array of deliverable IDs to be marked. Example: [3,4,5]
     *
     */

    public function selectAdditionalDeliverable(Request $request){
        try{
            $validatedData = $request->validate([
                'problemGoalId' => 'required|int',
                'additionalService' => 'present|array',
                'additionalService.*.additionalServiceId' => 'required|int',
                'additionalService.*.deliverableIds' => 'present|array',
            ]);
            $problemGoalId = $validatedData['problemGoalId'];
            $additionalService = $validatedData['additionalService'];
            $problemAndGoal = ProblemsAndGoals::findOrFail($problemGoalId);

            DB::beginTransaction();
            foreach($additionalService as $additionalService){
                Deliberable::where('problemGoalId', $problemGoalId)
                    ->whereIn('id', $additionalService['deliverableIds'])
                    ->where('additionalServiceId', $additionalService['additionalServiceId'])
                    ->update(['isChecked' => 1]);

                // Update the records that should not be checked
                Deliberable::where('problemGoalId', $problemGoalId)
                    ->whereNotIn('id', $additionalService['deliverableIds'])
                    ->where('additionalServiceId', $additionalService['additionalServiceId'])
                    ->update(['isChecked' => 0]);
            }

            DB::commit();

            $response = [
                'message' => 'Deliverable selected successfully',
            ];
            return response()->json($response, 201);
        }catch (\Exception $exception){
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }

    /**
     * Update Deliverable
     *
     * @group Deliverable
     *
     * @urlParam id int required Id of the Deliverable.
     * @bodyParam title string required
     *
     */

    public function update($id, Request $request){
        $validatedData = $request->validate([
            'title' => 'required|string',
        ]);

        $deliverableObj = Deliberable::with(['scopeOfWork','additionalServiceInfo'])->findOrFail($id);
        $deliverableObj->title = $request->title;
        $deliverableObj->save();

        $response = [
            'message' => 'Deliverable updated successfully',
            'data' => $deliverableObj,
        ];

        return response()->json($response, 201);
    }
}
