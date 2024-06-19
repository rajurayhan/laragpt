<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Http;
use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\MeetingLink;
use App\Models\MeetingTranscript;
use App\Models\ProjectSummary;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use App\Services\TldvService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


/**
 * @authenticated
 */

 class ProjectSummeryController extends Controller
{

    private $promptType = PromptType::MEETING_SUMMARY;

    /**
     * Get Project Summery List
     *
     * @group Project Summery
     *
     * @queryParam page integer page number.
     * @queryParam per_page integer page number.
     */
     public function index(Request $request)
     {
         $query = ProjectSummary::latest()->with('meetingTranscript','meetingTranscript.meetingLinks', 'meetingTranscript.serviceInfo', 'createdBy');

         // Paginate the results if a page number is provided
         if ($request->has('page')) {
             $meetings = $query->paginate($request->get('per_page')??10);
             return response()->json([
                 'data' => $meetings->items(),
                 'total' => $meetings->total(),
                 'current_page' => $meetings->currentPage(),
                 'per_page' => $meetings->perPage(),
             ]);
         }

         // Fetch all data if no page number is provided
         $meetings = $query->get();
         return response()->json([
             'data' => $meetings,
         ]);
     }

    /**
     * Create Project Summery
     *
     * @group Project Summery
     *
     * @bodyParam transcriptId integer The id of the transcript to regenerate.
     * @bodyParam projectName string required The name of the project.
     * @bodyParam serviceId integer required The type of the project.
     * @bodyParam company string required The company name of the project.
     * @bodyParam clientPhone string The phone number of the client.
     * @bodyParam clientEmail string The email of the client.
     * @bodyParam clientWebsite string The website of the client.
     * @bodyParam meetingLinks string[] required An array of meeting links. Example: ["https://tldv.io/app/meetings/663e283b70cff500132a9bbd"]
     */

    public function store(Request $request){
        $validatedData = $request->validate([
            'transcriptId' => 'nullable|integer',
            'projectName' => 'required|string',
            'projectType' => 'nullable|integer',
            'serviceId' => 'required|integer|exists:services,id',
            'company' => 'required|string',
            'clientPhone' => 'nullable|string',
            // 'clientPhone' => ['nullable', 'string', new USPhoneNumber],
            'clientEmail' => 'nullable|email',
            'clientWebsite' => 'nullable|string',
            'meetingLinks' => 'required|array',
        ]);
        //$meetingTranscript = MeetingTranscript::findOrFail(20);

        try{
            set_time_limit(500);
            $prompt = PromptService::findPromptByType($this->promptType);
            if($prompt == null){
                $response = [
                    'message' => 'Prompt not set for PromptType::MEETING_SUMMARY',
                    'data' => []
                ];
                return response()->json($response, 422);
            }


            DB::beginTransaction();
            $existingMeetingLinks = [];
            if($request->filled('transcriptId')) {
                $meetingTranscript = MeetingTranscript::find($request->transcriptId);
                $existingMeetingLinks = MeetingLink::where('transcriptId',$meetingTranscript->id)->get()->pluck('id')->toArray();;
            }else{
                $meetingTranscript = new MeetingTranscript();
            }

            $meetingTranscript->projectName = $request->projectName;
            $meetingTranscript->serviceId = $request->serviceId;
            $meetingTranscript->company = $request->company;
            $meetingTranscript->clientPhone = $request->clientPhone;
            $meetingTranscript->clientEmail = $request->clientEmail;
            $meetingTranscript->clientWebsite = $request->clientWebsite;
            $meetingTranscript->save();

            $transcriptText1stValue = null;
            foreach($validatedData['meetingLinks'] as $index => $link){
                $tldv = new TldvService();
                $transcriptText = $tldv->getTranscriptFromUrl($link);
                if($index === 0){
                    $transcriptText1stValue = $transcriptText;
                }
                $meetingLink = new MeetingLink();
                $meetingLink->transcriptId = $meetingTranscript->id;
                $meetingLink->meetingLink = $link;
                $meetingLink->transcriptText = $transcriptText;
                $meetingLink->serial = $index + 1;
                $meetingLink->save();
            }
            if(is_array($existingMeetingLinks) && count($existingMeetingLinks) > 0){
                MeetingLink::whereIn('id',$existingMeetingLinks)->delete();
            }

            $meetingTranscript = $meetingTranscript->load(['meetingLinks','serviceInfo']);


            $response = Http::timeout(450)->post(env('AI_APPLICATION_URL').'/estimation/transcript-generate', [
                'transcript' => $transcriptText1stValue,
                'prompt' => $prompt->prompt,
            ]);
            if (!$response->successful()) {
                WebApiResponse::error(500, $errors = [], "Can't able to generate transcript, Please try again.");
            }
            Log::info(['Summery Generate AI.',$response]);
            $data = $response->json();

            $projectSummeryObj = ProjectSummary::updateOrCreate(
                ['transcriptId' => $meetingTranscript->id],
                ['summaryText' => $data['data']['summery']]
            );
            $meetingTranscript->assistantId = $data['data']['assistantId'];
            $meetingTranscript->threadId = $data['data']['threadId'];
            $meetingTranscript->save();
            $projectSummeryObj->meetingTranscript = $meetingTranscript;

            DB::commit();
            $response = [
                'message' => 'Created Successfully',
                'data' => $projectSummeryObj->load('createdBy')
            ];

            return response()->json($response, 201);

        }catch (\Exception $exception){
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }

    }


    /**
     * Show Project Summery
     *
     * @group Project Summery
     *
     * @urlParam id int Id of the transcript.
     */
    public function show($id){
        $projectSummeryObj = ProjectSummary::with(
            ['meetingTranscript','meetingTranscript.serviceInfo','meetingTranscript.meetingLinks','meetingTranscript.problemsAndGoals.projectOverview', 'meetingTranscript.problemsAndGoals.scopeOfWork.deliverables']
        )->findOrFail($id);
        if(!empty($projectSummeryObj->meetingTranscript->problemsAndGoals->id)){
            $scopeOfWorksData = ScopeOfWorkController::getScopeOfWorks($projectSummeryObj->meetingTranscript->problemsAndGoals->id);
        }else{
            $scopeOfWorksData = [
                'scopeOfWorks' => [],
                'additionalServices' => [],
            ];
        }

        if(!empty($projectSummeryObj->meetingTranscript->problemsAndGoals->id)){
            $deliverablesData = DeliverablesController::getDeliverables($projectSummeryObj->meetingTranscript->problemsAndGoals->id);
        }else{
            $deliverablesData = [
                'deliverables' => [],
                'deliverableNotes' => [],
            ];
        }
        $projectSummeryObj->scopeOfWorksData = $scopeOfWorksData;
        $projectSummeryObj->deliverablesData = $deliverablesData;

        $response = [
            'message' => 'Data Showed Successfully',
            'data' => $projectSummeryObj
        ];

        return response()->json($response, 201);
    }

    /**
     * Update Project Summery
     *
     * @group Project Summery
     *
     * @urlParam id int Id of the transcript.
     * @bodyParam summaryText int required summaryText of the Project Summery.
     */

    public function update($id, Request $request){

        $validatedData = $request->validate([
            'summaryText' => 'required|string',
        ]);

        $projectSummeryObj = ProjectSummary::with('meetingTranscript','meetingTranscript.serviceInfo','meetingTranscript.meetingLinks')->find($id);
        $projectSummeryObj->summaryText = $request->summaryText;

        $projectSummeryObj->save();
        $response = [
            'message' => 'Updated Successfully',
            'data' => $projectSummeryObj
        ];

        return response()->json($response, 201);
    }

    /**
     * Delete Project Summery
     *
     * @group Project Summery
     * @urlParam id int Id of the transcript.
     */
    public function delete($id){
        $projectSummeryObj = ProjectSummary::findOrFail($id);
        if($projectSummeryObj->meetingTranscript->problemsAndGoals){
            $projectSummeryObj->meetingTranscript->problemsAndGoals->delete();
            if($projectSummeryObj->meetingTranscript->problemsAndGoals->scopeOfWork){
                $projectSummeryObj->meetingTranscript->problemsAndGoals->scopeOfWork->delete();
                if($projectSummeryObj->meetingTranscript->problemsAndGoals->scopeOfWork->deliverables){
                    $projectSummeryObj->meetingTranscript->problemsAndGoals->scopeOfWork->deliverables->delete();
                }
            }
            if($projectSummeryObj->meetingTranscript->problemsAndGoals->projectOverview){
                $projectSummeryObj->meetingTranscript->problemsAndGoals->projectOverview->delete();
            }
        }
        $projectSummeryObj->delete();
        if($projectSummeryObj->meetingTranscript){
            MeetingLink::where('transcriptId',$projectSummeryObj->meetingTranscript->id)->delete();
            $projectSummeryObj->meetingTranscript->delete();
        }
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 201);
    }

    // /**
    //  * Get Meeting Summery List
    //  *
    //  * @group Meeting Summery
    //  *
    //  * @queryParam page integer page number.
    //  */
    // public function indexMeetingSummery(){
    //     $meetings = MeetingSummery::paginate(10);
    //     return response()->json([
    //         'data' => $meetings->items(),
    //         'total' => $meetings->total(),
    //         'current_page' => $meetings->currentPage(),
    //     ]);

    // }

    // /**
    //  * Create Meeting Summery
    //  *
    //  * @group Meeting Summery
    //  *
    //  * @bodyParam transcriptText string required The text of the transcript.
    //  */

    // public function storeMeetingSummery(Request $request){
    //     set_time_limit(500);
    //     $validatedData = $request->validate([
    //         'transcriptText' => 'required|string',
    //     ]);

    //     // Generate Summery
    //     $summery = OpenAIGeneratorService::generateMeetingSummery($request->transcriptText);

    //     $meetingSummeryObj = new MeetingSummery();
    //     $meetingSummeryObj->meetingSummeryText = $summery;

    //     $meetingSummeryObj->save();

    //     $response = [
    //         'message' => 'Created Successfully',
    //         'data' => $meetingSummeryObj
    //     ];

    //     return response()->json($response, 201);
    // }

    // /**
    //  * Update Meeting Summery
    //  *
    //  * @group Meeting Summery
    //  *
    //  * @urlParam id int Id of the transcript.
    //  * @bodyParam summaryText int required summaryText of the Project Summery.
    //  */

    // public function updateMeetingSummery($id, Request $request){

    //     $validatedData = $request->validate([
    //         'summaryText' => 'required|string',
    //     ]);

    //     $meetingSummeryObj = MeetingSummery::find($id);
    //     $meetingSummeryObj->meetingSummeryText = $request->summaryText;

    //     $meetingSummeryObj->save();
    //     $response = [
    //         'message' => 'Updated Successfully',
    //         'data' => $meetingSummeryObj
    //     ];

    //     return response()->json($response, 201);
    // }

    // /**
    //  * Show Meeting Summery
    //  *
    //  * @group Meeting Summery
    //  *
    //  * @urlParam id int Id of the transcript.
    //  */
    // public function showMeetingSummery($id){
    //     $meetingSummeryObj = MeetingSummery::find($id);
    //     $response = [
    //         'message' => 'Data Showed Successfully',
    //         'data' => $meetingSummeryObj
    //     ];

    //     return response()->json($response, 201);
    // }

    // /**
    //  * Meeting Summery
    //  *
    //  * @group Meeting Summery
    //  * @urlParam id int Id of the transcript.
    //  */
    // public function deleteMeetingSummery($id){
    //     $meetingSummeryObj = MeetingSummery::find($id);
    //     $meetingSummeryObj->delete();
    //     $response = [
    //         'message' => 'Deleted Successfully',
    //         'data' => []
    //     ];

    //     return response()->json($response, 201);
    // }
}
