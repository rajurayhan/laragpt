<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\MeetingSummery;
use App\Models\Prompt;
use App\Services\ClickUpCommentUploader;
use App\Services\TldvService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
     * @queryParam meetingName string Filter by meetingName.
     * @queryParam meetingType string Filter by meetingType.
     * @queryParam createdById string Filter by createdById.
     * @queryParam page integer page number.
     */
    public function indexMeetingSummery(Request $request){

        $adminUsers = [1,5]; // 1. Josh, 5. Raju

        $currentUser = auth()->user();
        $query = MeetingSummery::query();
        if(!in_array($currentUser->id , $adminUsers)){
            $query->where(function($query) use ($currentUser) {
                $query->where('is_private', false)
                    ->orWhere(function($query) use ($currentUser) {
                        $query->where('is_private', true)
                                ->where('createdById', $currentUser->id);
                    });
            });
        }
            // ->with('createdBy')->latest()
            // ->paginate(10);

        if($request->filled('meetingName')){
            $query->where('meetingName', 'like', '%' . $request->input('meetingName') . '%');
        }

        if($request->filled('meetingType')){
            $query->where('meetingType', $request->input('meetingType'));
        }

        if($request->filled('createdById')){
            $query->where('createdById', $request->input('createdById'));
        }

        $meetings = $query->with('createdBy', 'meetingTypeData')->latest()->paginate(10);

        // $meetingsData = $meetings->items();

        $meetingsData = array_map(function ($meeting) {
            $meeting->meeting_type_id = $meeting->meetingTypeData ? $meeting->meetingTypeData->id : null;
            $meeting->meeting_type = $meeting->meetingTypeData ?? null;
            return $meeting;
        }, $meetings->items());



        return response()->json([
            'data' => $meetingsData,
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
        set_time_limit(500);
        $prompts = Prompt::where('type',$this->promptType)->orderBy('serial','ASC')->get();
        if(count($prompts) <1 ){
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
            $transcript = TldvService::getTranscriptFromUrl($request->tldvLink);
            // $meetingId = $this->getLastPartOfUrl($request->tldvLink);

            // if(isset($meetingId)){
            //     $transcript = $this->getTldvTranscript($meetingId);
            // }
        }

        //start
        $response = Http::timeout(450)->post(env('AI_APPLICATION_URL').'/estimation/meeting-summery-generate', [
            'transcript' => isset($transcript) ? $transcript : $request->transcriptText,
            'prompts' => $prompts->map(function ($item, $key) {
                return [
                    'prompt_text'=> $item->prompt,
                    'action_type'=> $item->action_type,
                ];
            })->toArray(),
        ]);
        if (!$response->successful()) {
            return WebApiResponse::error(500, $errors = [], "Can't able to generate transcript, Please try again.");
        }
        Log::info(['Meeting Summery Generate AI.',$response]);
        $data = $response->json();
        // Generate Summery

        $meetingSummeryObj = new MeetingSummery();
        $meetingSummeryObj->meetingName = $request->meetingName;
        $meetingSummeryObj->meetingType = $request->meetingType;
        $meetingSummeryObj->tldvLink = $request->tldvLink;
        $meetingSummeryObj->is_private = $request->is_private ?? null;
        $meetingSummeryObj->clickupLink = $request->clickupLink;
        $meetingSummeryObj->meetingSummeryText = $data['data']['summery'];
        $meetingSummeryObj->transcriptText = isset($transcript) ? $transcript : $request->transcriptText;

        $meetingSummeryObj->save();

        $meetingSummeryObj = MeetingSummery::where('id',$meetingSummeryObj->id)->first();

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
        $meetingSummeryObj->is_private = $request->is_private ?? null;
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
        $htmlData = $meetingSummeryObj->meetingSummeryText;
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
        $apiKey = env('TLDV_API_KEY', '77473b23-47cf-471f-8f4e-adbb7605b0f0');

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
