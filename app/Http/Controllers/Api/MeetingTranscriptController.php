<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeetingTranscript;
use App\Models\ProjectSummary;
use App\Services\OpenAIGeneratorService;
use Illuminate\Http\Request;

class MeetingTranscriptController extends Controller
{
    public function index(){
        $meetings = MeetingTranscript::paginate(10);
        return response()->json([
            'data' => $meetings->items(),
            'total' => $meetings->total(),
            'current_page' => $meetings->currentPage(),
        ]);

    }

    /**
     * Creae Project Summery
     * @group Project Summery
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

        $meetingObj->projectSummary = $projectSummeryObj;

        $response = [
            'message' => 'Created Successfully',
            'data' => $meetingObj
        ];

        return response()->json($response, 201);
    }
}
