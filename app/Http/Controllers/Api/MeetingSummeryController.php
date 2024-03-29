<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Models\MeetingSummery;
use App\Services\ClickUpCommentUploader;
use App\Services\Markdown2Html;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * @authenticated
 */

 class MeetingSummeryController extends Controller
{
    private $promptType = PromptType::MEETING_SUMMARY;
    /**
     * Get Meeting Summery List
     *
     * @group Meeting Summery
     *
     * @queryParam page integer page number.
     */
    public function indexMeetingSummery(Request $request){
        // $meetings = MeetingSummery::with('createdBy')->latest()->paginate(10);
        $currentUser = auth()->user(); 
        $meetings = MeetingSummery::where(function($query) use ($currentUser) {
            // Public summaries
            $query->where('is_private', false)
                  ->orWhere(function($query) use ($currentUser) {
                      // Private summaries created by the current user
                      $query->where('is_private', true)
                            ->where('createdById', $currentUser->id);
                  });
        })
        ->paginate($request->page ?? 10);
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
     * @bodyParam clickupLink string Task url for the meeting.
     * @bodyParam tldvLink string Tldv meeting url.
     * @bodyParam is_private boolean Privacy of the meeting summery. If true, it will be only available to it's creator.
     */

    public function storeMeetingSummery(Request $request){
        // return $taskId = $this->getLastPartOfUrl($request->clickupLink);
        // return $this->getLastPartOfUrl($request->tldvLink);
        set_time_limit(1500);
        $prompt = PromptService::findPromptByType($this->promptType);
        if($prompt == null){
            $response = [
                'message' => 'Prompt not set for PromptType::MEETING_SUMMARY',
                'data' => []
            ];
            return response()->json($response, 422);
        }
        $validatedData = $request->validate([
            'clickupLink' => 'required|string',
            'tldvLink' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'transcriptText' => 'required_without:tldvLink',
            'meetingName' => 'required|string',
            'meetingType' => 'required|integer',
        ]);

        if($request->filled('tldvLink')){
            $meetingId = $this->getLastPartOfUrl($request->tldvLink);

            if(isset($meetingId)){
                $transcript = $this->getTldvTranscript($meetingId);
            }
        }

        // Generate Summery
        $summery = OpenAIGeneratorService::generateMeetingSummery(isset($transcript) ? $transcript : $request->transcriptText, $prompt->prompt);

        $meetingSummeryObj = new MeetingSummery();
        $meetingSummeryObj->meetingName = $request->meetingName;
        $meetingSummeryObj->meetingType = $request->meetingType;
        $meetingSummeryObj->tldvLink = $request->tldvLink;
        $meetingSummeryObj->clickupLink = $request->clickupLink;
        $meetingSummeryObj->meetingSummeryText = $summery;
        $meetingSummeryObj->transcriptText = isset($transcript) ? $transcript : $request->transcriptText;

        $meetingSummeryObj->save();

        // Push to clickup

        // $taskId = $this->getLastPartOfUrl($request->clickupLink);
        // if($taskId){
        //     $clickupUploader = new ClickUpCommentUploader($taskId, $summery);
        //     $clickupUploader->pushComment();
        // }

        $response = [
            'message' => 'Created Successfully',
            'data' => $meetingSummeryObj->load('createdBy')
        ];

        return response()->json($response, 201);
    }

    /**
     * Update Meeting Summery
     *
     * @group Meeting Summery
     *
     * @urlParam id int Id of the transcript.
     * @bodyParam transcriptText string required The text of the transcript.
     * @bodyParam meetingName string required Name of the meeting.
     * @bodyParam meetingType integer required Meeting type [1: Client, 2: Intenal].
     * @bodyParam clickupLink string Task url for the meeting.
     * @bodyParam tldvLink string Tldv meeting url.
     * @bodyParam is_private boolean Privacy of the meeting summery. If true, it will be only available to it's creator.
     */

    public function updateMeetingSummery($id, Request $request){
        $validatedData = $request->validate([
            'pushToClickUp' => 'required|boolean',
            'clickupLink' => 'required_if:pushToClickUp,true',
            'summaryText' => 'required|string',
            'tldvLink' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'transcriptText' => 'required_without:tldvLink',
            'meetingName' => 'required|string',
            'meetingType' => 'required|integer',
        ]);

        // return $validatedData;

        $meetingSummeryObj = MeetingSummery::find($id);
        $meetingSummeryObj->meetingSummeryText = $request->summaryText;
        $meetingSummeryObj->meetingName = $request->meetingName;
        $meetingSummeryObj->meetingType = $request->meetingType;
        $meetingSummeryObj->transcriptText = $request->transcriptText;
        $meetingSummeryObj->tldvLink = $request->tldvLink;
        $meetingSummeryObj->clickupLink = $request->clickupLink;

        $meetingSummeryObj->save();

        // Push to clickup

        $taskId = $this->getLastPartOfUrl($request->clickupLink);
        if($taskId && $request->pushToClickUp == true){
            $clickupUploader = new ClickUpCommentUploader($taskId, $request->summaryText);
            $clickupUploader->pushComment();
        }

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
        $htmlData = Markdown2Html::convert($meetingSummeryObj->meetingSummeryText);
        $meetingSummeryObj->htmlText = html_entity_decode((string)$htmlData);
        $meetingSummeryObj->summaryText = $meetingSummeryObj->meetingSummeryText ?? null;
        // $meetingSummeryObj->htmlText = (string)$htmlData;
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

    private function getLastPartOfUrl($url){
        // Parse the URL
        $parsedUrl = parse_url($url);

        // Get the path part
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : null;

        // Get the last segment of the path
        $lastSegment = basename($path);

        return $lastSegment;
    }

    private function getTldvTranscript($id){
        $apiUrl = 'https://pasta.tldv.io/v1alpha1/meetings/'.$id.'/transcript';
        $apiKey = env('TLDV_API_KEY', 'd78e73c0-f2d8-468d-87c2-140518f98846');

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])->get($apiUrl);

            // Check if the request was successful (status code 2xx)
            if ($response->successful()) {
                $data = $response->json(); // Get the response data
                $transcript = '';
                foreach ($data['data'] as $key => $content) {
                    $transcript .= $content['speaker'] .': '. $content['text'];
                    $transcript.= PHP_EOL;
                    $transcript.= PHP_EOL;
                }
                return $transcript;
            } else {
                $errorMessage = $response->status() . ' ' . $response->reason();
                return null;
            }
        } catch (\Exception $exception) {
            return null;
        }
    }
}
