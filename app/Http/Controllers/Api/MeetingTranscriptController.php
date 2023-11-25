<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeetingTranscript;
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
     * Creae New Meeting
     * @group Meeting Transcript
     *
     * @bodyParam transcriptText string required The text of the transcript.
     * @bodyParam projectName string required The name of the project.
     * @bodyParam clientPhone string The phone number of the client.
     * @bodyParam clientEmail string The email of the client.
     * @bodyParam clientWebsite string The website of the client.
     */

    public function store(Request $request){
        $validatedData = $request->validate([
            'transcriptText' => 'required|string',
            'projectName' => 'required|string',
            'clientPhone' => 'nullable|string',
            'clientEmail' => 'nullable|email',
            'clientWebsite' => 'nullable|url',
        ]);
    
        $meeting = MeetingTranscript::create($validatedData);

        $response = [
            'message' => 'Created Successfully',
            'data' => $meeting
        ];
    
        return response()->json($response, 201);
    }
}
