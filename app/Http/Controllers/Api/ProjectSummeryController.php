<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProjectType;
use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Models\MeetingLink;
use App\Models\MeetingSummery;
use App\Models\MeetingTranscript;
use App\Models\ProjectSummary;
use App\Rules\USPhoneNumber;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use App\Services\TldvService;
use Illuminate\Http\Request;

/**
 * @authenticated
 */

 class ProjectSummeryController extends Controller
{

    private $promptType = PromptType::MEETING_SUMMARY;

    /**
     * Get SOW Meeting Summery List
     *
     * @group SOW Meeting Summery
     *
     * @queryParam page integer page number.
     * @queryParam perPage integer page number.
     */
     public function index(Request $request)
     {
         $query = ProjectSummary::latest()->with('meetingTranscript','meetingTranscript.meetingLinks', 'createdBy');

         // Paginate the results if a page number is provided
         if ($request->has('page')) {
             $meetings = $query->paginate($request->filled('perPage') ? $request->perPage : 10);
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
     * Create SOW Meeting Summery
     *
     * @group SOW Meeting Summery
     *
     * @bodyParam transcriptId integer The id of the transcript to regenerate.
     * @bodyParam transcriptText string required The text of the transcript.
     * @bodyParam projectTypePrefix string required The name of the project.
     * @bodyParam projectName string required The name of the project.
     * @bodyParam projectTypeId integer required The type of the project.
     * @bodyParam company string required The company name of the project.
     * @bodyParam clientPhone string The phone number of the client.
     * @bodyParam clientEmail string The email of the client.
     * @bodyParam clientWebsite string The website of the client.
     * @bodyParam meetingLinks array required An array of meeting links. Example: ['https://tldv.io/app/meetings/663e283b70cff500132a9bbd']
     */

    public function store(Request $request){
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
            $validatedData = $request->validate([
                'transcriptId' => 'nullable|integer',
                'projectTypePrefix' => 'required|string',
                'projectName' => 'required|string',
                'projectType' => 'nullable|integer',
                'projectTypeId' => 'required|integer|exists:project_types,id',
                'company' => 'required|string',
                'clientPhone' => 'nullable|string',
                // 'clientPhone' => ['nullable', 'string', new USPhoneNumber],
                'clientEmail' => 'nullable|email',
                'clientWebsite' => 'nullable|string',
                'meetingLinks' => 'required|array',
            ]);
            //$meetingTranscript = MeetingTranscript::findOrFail(20);


            $existingMeetingLinks = [];
            if($request->filled('transcriptId')) {
                $meetingTranscript = MeetingTranscript::findOrFail($request->transcriptId);
                $existingMeetingLinks = MeetingLink::where('transcriptId',$meetingTranscript->id)->get()->pluck('id')->toArray();;
            }else{
                $meetingTranscript = new MeetingTranscript();
            }
            $meetingTranscript->projectName = $request->projectName;
            $meetingTranscript->projectType = $request->projectTypeId;
            $meetingTranscript->projectTypeId = $request->projectTypeId;
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

            $meetingTranscript = $meetingTranscript->load(['meetingLinks']);



            // Generate Summery
            $summery = OpenAIGeneratorService::generateSummery($transcriptText1stValue, $prompt->prompt);

            // $projectSummeryObj = new ProjectSummary();
            // $projectSummeryObj->summaryText = $summery;
            // $projectSummeryObj->transcriptId = $meetingObj->id;

            // $projectSummeryObj->save();

            $projectSummeryObj = ProjectSummary::updateOrCreate(
                ['transcriptId' => $meetingTranscript->id],
                ['summaryText' => $summery]
            );

            $projectSummeryObj->meetingTranscript = $meetingTranscript;

            $response = [
                'message' => 'Created Successfully',
                'data' => $projectSummeryObj->load('createdBy')
            ];

            return response()->json($response, 201);

        }catch (\Exception $exception){
            return response()->json([
                'message'=> 'Failed to save transcript. Please try again.',
                'error' => $exception->getMessage()
            ], 500);
        }

    }


    /**
     * Show SOW Meeting Summery
     *
     * @group SOW Meeting Summery
     *
     * @urlParam id int Id of the transcript.
     */
    public function show($id){
        $projectSummeryObj = ProjectSummary::with(
            ['meetingTranscript','meetingTranscript.meetingLinks','meetingTranscript.problemsAndGoals.projectOverview', 'meetingTranscript.problemsAndGoals.scopeOfWork.deliverables']
        )->findOrFail($id);
        $response = [
            'message' => 'Data Showed Successfully',
            'data' => $projectSummeryObj
        ];

        return response()->json($response, 201);
    }

//    /**
//     * Update SOW Meeting Summery
//     *
//     * @group SOW Meeting Summery
//     *
//     * @urlParam id int Id of the transcript.
//     * @bodyParam summaryText int required summaryText of the SOW Meeting Summery.
//     */

    /*public function update($id, Request $request){

        $validatedData = $request->validate([
            'summaryText' => 'required|string',
        ]);

        $projectSummeryObj = ProjectSummary::with('meetingTranscript')->find($id);
        $projectSummeryObj->summaryText = $request->summaryText;

        $projectSummeryObj->save();
        $response = [
            'message' => 'Updated Successfully',
            'data' => $projectSummeryObj
        ];

        return response()->json($response, 201);
    }*/

    /**
     * Delete SOW Meeting Summery
     *
     * @group SOW Meeting Summery
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
    //  * @bodyParam summaryText int required summaryText of the SOW Meeting Summery.
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
