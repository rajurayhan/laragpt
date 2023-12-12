<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeetingSummery;
use App\Services\OpenAIGeneratorService;
use Illuminate\Http\Request;

class MeetingSummeryController extends Controller
{
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
     * @bodyParam meetingName string required Name of the meeting.
     * @bodyParam meetingType integer required Meeting type [1: Client, 2: Intenal].
     */

    public function storeMeetingSummery(Request $request){
        set_time_limit(500);
        $validatedData = $request->validate([
            'transcriptText' => 'required|string',
            'meetingName' => 'required|string',
            'meetingType' => 'required|integer',
        ]);

        // Generate Summery
        $summery = OpenAIGeneratorService::generateMeetingSummery($request->transcriptText);

        $meetingSummeryObj = new MeetingSummery();
        $meetingSummeryObj->meetingName = $request->meetingName;
        $meetingSummeryObj->meetingType = $request->meetingType;
        $meetingSummeryObj->meetingSummeryText = $summery;
        $meetingSummeryObj->transcriptText = $request->transcriptText;

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
     * @bodyParam meetingName string required Name of the meeting.
     * @bodyParam meetingType integer required Meeting type [1: Client, 2: Intenal].
     */

    public function updateMeetingSummery($id, Request $request){

        $validatedData = $request->validate([
            'summaryText' => 'required|string',
            'meetingName' => 'required|string',
            'meetingType' => 'required|integer',
        ]);

        $meetingSummeryObj = MeetingSummery::find($id);
        $meetingSummeryObj->meetingSummeryText = $request->summaryText;
        $meetingSummeryObj->meetingName = $request->meetingName;
        $meetingSummeryObj->meetingType = $request->meetingType;
        $meetingSummeryObj->transcriptText = $request->transcriptText;

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
