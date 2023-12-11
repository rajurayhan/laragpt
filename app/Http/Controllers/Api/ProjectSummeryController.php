<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeetingSummery;
use App\Models\MeetingTranscript;
use App\Models\ProjectSummary;
use App\Services\OpenAIGeneratorService;
use Illuminate\Http\Request;

class ProjectSummeryController extends Controller
{

    /**
     * Get SOW Meeting Summery List
     *
     * @group SOW Meeting Summery
     *
     * @queryParam page integer page number.
     */
    public function index(){
        $meetings = ProjectSummary::with('meetingTranscript')->paginate(10);
        return response()->json([
            'data' => $meetings->items(),
            'total' => $meetings->total(),
            'current_page' => $meetings->currentPage(),
        ]);

    }

    /**
     * Create SOW Meeting Summery
     *
     * @group SOW Meeting Summery
     *
     * @bodyParam transcriptText string required The text of the transcript.
     * @bodyParam projectName string required The name of the project.
     * @bodyParam clientPhone string The phone number of the client.
     * @bodyParam clientEmail string The email of the client.
     * @bodyParam clientWebsite string The website of the client.
     */

    public function store(Request $request){
        set_time_limit(500);
        $validatedData = $request->validate([
            'transcriptText' => 'required|string',
            'projectName' => 'required|string',
            'clientPhone' => 'nullable|string',
            'clientEmail' => 'nullable|email',
            'clientWebsite' => 'nullable|url',
        ]);
        $meetingObj = MeetingTranscript::create($validatedData);

        $meetingObj->save();

        // Generate Summery
        $summery = OpenAIGeneratorService::generateSummery($request->transcriptText);

        $projectSummeryObj = new ProjectSummary();
        $projectSummeryObj->summaryText = $summery;
        $projectSummeryObj->transcriptId = $meetingObj->id;

        $projectSummeryObj->save();

        $projectSummeryObj->meetingTranscript = $meetingObj;

        $response = [
            'message' => 'Created Successfully',
            'data' => $projectSummeryObj
        ];

        return response()->json($response, 201);
    }


    /**
     * Show SOW Meeting Summery
     *
     * @group SOW Meeting Summery
     *
     * @urlParam id int Id of the transcript.
     */
    public function show($id){
        $projectSummeryObj = ProjectSummary::with(['meetingTranscript.problemsAndGoals.projectOverview', 'meetingTranscript.problemsAndGoals.scopeOfWork'])->find($id);
        $response = [
            'message' => 'Data Showed Successfully',
            'data' => $projectSummeryObj
        ];

        return response()->json($response, 201);
    }

    /**
     * Update SOW Meeting Summery
     *
     * @group SOW Meeting Summery
     *
     * @urlParam id int Id of the transcript.
     * @bodyParam summaryText int required summaryText of the SOW Meeting Summery.
     */

    public function update($id, Request $request){

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
    }

    /**
     * Delete SOW Meeting Summery
     *
     * @group SOW Meeting Summery
     * @urlParam id int Id of the transcript.
     */
    public function delete($id){
        $projectSummeryObj = ProjectSummary::find($id);
        $projectSummeryObj->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 201);
    }

    /**
     * Get Meeting Summery List
     *
     * @group Meeting Summery
     *
     * @queryParam page integer page number.
     */
    public function indexMeetingSummery(){
        $meetings = MeetingSummery::paginate(10);
        return response()->json([
            'data' => $meetings->items(),
            'total' => $meetings->total(),
            'current_page' => $meetings->currentPage(),
        ]);

    }

    /**
     * Create Meeting Summery
     *
     * @group Meeting Summery
     *
     * @bodyParam transcriptText string required The text of the transcript.
     */

    public function storeMeetingSummery(Request $request){
        set_time_limit(500);
        $validatedData = $request->validate([
            'transcriptText' => 'required|string',
        ]);

        // Generate Summery
        $summery = OpenAIGeneratorService::generateMeetingSummery($request->transcriptText);

        $meetingSummeryObj = new MeetingSummery();
        $meetingSummeryObj->meetingSummeryText = $summery;

        $meetingSummeryObj->save();

        $response = [
            'message' => 'Created Successfully',
            'data' => $meetingSummeryObj
        ];

        return response()->json($response, 201);
    }

    /**
     * Update Meeting Summery
     *
     * @group Meeting Summery
     *
     * @urlParam id int Id of the transcript.
     * @bodyParam summaryText int required summaryText of the SOW Meeting Summery.
     */

    public function updateMeetingSummery($id, Request $request){

        $validatedData = $request->validate([
            'summaryText' => 'required|string',
        ]);

        $meetingSummeryObj = MeetingSummery::find($id);
        $meetingSummeryObj->meetingSummeryText = $request->summaryText;

        $meetingSummeryObj->save();
        $response = [
            'message' => 'Updated Successfully',
            'data' => $meetingSummeryObj
        ];

        return response()->json($response, 201);
    }

    /**
     * Show Meeting Summery
     *
     * @group Meeting Summery
     *
     * @urlParam id int Id of the transcript.
     */
    public function showMeetingSummery($id){
        $meetingSummeryObj = MeetingSummery::find($id);
        $response = [
            'message' => 'Data Showed Successfully',
            'data' => $meetingSummeryObj
        ];

        return response()->json($response, 201);
    }

    /**
     * Meeting Summery
     *
     * @group Meeting Summery
     * @urlParam id int Id of the transcript.
     */
    public function deleteMeetingSummery($id){
        $meetingSummeryObj = MeetingSummery::find($id);
        $meetingSummeryObj->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 201);
    }
}
