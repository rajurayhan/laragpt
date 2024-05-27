<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\Deliberable;
use App\Models\DeliverablesNotes;
use App\Models\ProblemsAndGoals;
use App\Models\ScopeOfWork;
use App\Models\ServiceDeliverables;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $deliverables = Deliberable::latest()->where('problemGoalId',$validatedData['problemGoalId'])->get();;


        // Fetch all data if no page number is provided

        return response()->json([
            'deliverables' => $deliverables,
        ]);
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
            return response()->json($scopeWork, 201);

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
        $prompt = PromptService::findPromptByType($this->promptType);
        if($prompt == null){
            $response = [
                'message' => 'Prompt not set for PromptType::DELIVERABLES',
                'data' => []
            ];
            return response()->json($response, 422);
        }

        $findExisting = Deliberable::where('problemGoalID',$validatedData['problemGoalId'])->first();

        if($findExisting){
            return WebApiResponse::error(500, $errors = [], 'The deliverable already generated.');
        }


        $problemAndGoal = ProblemsAndGoals::with(['meetingTranscript'])->where('id',$validatedData['problemGoalId'])->firstOrFail();
        $scopeOfWorks = ScopeOfWork::with(['meetingTranscript','deliverables'])
            ->where('problemGoalID', $validatedData['problemGoalId'])->where('isChecked', 1)
            ->get();

        $serviceScopeList = $scopeOfWorks->filter(function ($value) {
            return !empty($value->serviceScopeId);
        });

        $scopeDeliveryList = $serviceScopeList->reduce(function ($carry, $item) {
            return $carry->merge($item->deliverables->map(function ($delivery) use($item){
                unset($item->deliverables);
                $delivery->scopeOfWork = $item;
                $delivery->name = strip_tags($delivery->name);
                return $delivery;
            }));
        },collect([]));


        $scopeOfWorksKeyById = $scopeOfWorks->keyBy('id');
        $serviceAiScopeListJson = ($scopeOfWorks->filter(function ($value) {
            return empty($value->serviceScopeId);
        })->map(function($scopeOfWork){
            return [
                'scopeOfWorkId' => $scopeOfWork->id,
                'title' => strip_tags($scopeOfWork->title),
                'scopeText' => strip_tags($scopeOfWork->scopeText),
            ];
        }))->toJson();
        $deliverables = OpenAIGeneratorService::generateDeliverables($serviceAiScopeListJson, $prompt->prompt);

        if (!is_array($deliverables) || count($deliverables) < 1 || !isset($deliverables[0]['title'])) {
            return WebApiResponse::error(500, $errors = [], 'The merged result from AI is not expected output, Try again please');
        }

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

        $deliverableList = Deliberable::where('problemGoalId', $request->get('problemGoalId'))->get();
        return response()->json($deliverableList, 201);
    }

    /**
     * Update select Deliverable
     *
     * @group Deliverable
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam deliverableId string[] required An array of meeting links. Example: [1,2,3]
     * @bodyParam notes array required An array of component details.
     * @bodyParam notes.*.noteLink string required. Example: "https://tldv.io/app/meetings/663e283b70cff500132a9bbd"
     * @bodyParam notes.*.note string required. Example: "lorem ipsum"
     *
     */

    public function select(Request $request){
        try{
            $validatedData = $request->validate([
                'problemGoalId' => 'required|int',
                'deliverableId' => 'required|array',
                'notes' => 'present|array',
                'notes.*.note' => 'required|string',
                'notes.*.noteLink' => 'required|url',
            ]);
            $problemGoalId = $validatedData['problemGoalId'];
            $deliverableIds = $validatedData['deliverableId'];
            $notes = $validatedData['notes'];
            $problemAndGoal = ProblemsAndGoals::findOrFail($problemGoalId);

            DB::beginTransaction();
            Deliberable::where('problemGoalId', $problemGoalId)
                ->whereIn('id', $deliverableIds)
                ->whereNull('additionalServiceId')
                ->update(['isChecked' => 1]);

            // Update the records that should not be checked
            Deliberable::where('problemGoalId', $problemGoalId)
                ->whereNotIn('id', $deliverableIds)
                ->whereNull('additionalServiceId')
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

        $scopeOfWork = Deliberable::findOrFail($id);
        $scopeOfWork->title = $request->title;
        $scopeOfWork->save();

        $response = [
            'message' => 'Deliverable updated successfully',
            'data' => $scopeOfWork,
        ];

        return response()->json($response, 201);
    }
}
