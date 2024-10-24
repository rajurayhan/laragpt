<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationSharedUser;
use App\Models\ProjectSummary;
use App\Models\Prompt;
use App\Services\OpenAIGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * @group Conversations
 * @authenticated
 *
 * APIs for managing conversations.
 */
class ConversationController extends Controller
{
    /**
     * Get all Conversations
     *
     * Get a list of all Conversations.
     *
     * @queryParam page integer page number.
     * @queryParam name string Filter by name.
     * @queryParam per_page integer Number of items per page.
     */
    public function index(Request $request)
    {
        try {
            $query = Conversation::query();

            $user  = Auth::user();

            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            if ($request->filled('user_id')) {
                $query->where('user_id',  $request->input('user_id'));
            }

            $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified
            // if($user->hasRole('Admin')){
            //     $conversations = $query->with('user', 'messages','shared_user.user')->latest()->paginate($perPage);
            // }
            // else{
            //     // $conversations = $query->where('user_id', $user->id)->with('user', 'messages', 'shared_user.user')->latest()->paginate($perPage);
            //     $conversations = $query->where('user_id', $user->id)->orWhereHas('shared_user', function($subQuery) use ($user){
            //         $subQuery->where('user_id', $user->id);
            //     })->with('user', 'messages', 'shared_user.user')->latest()->paginate($perPage);
            // }

            if ($user->hasRole('Admin')) {
                $conversations = $query->with(['user', 'messages' => function ($query) {
                    $query->latest('created_at'); // Order messages by `created_at`
                }, 'shared_user.user'])
                ->orderBy(
                    // Order the conversations by the latest message's `created_at`
                    ConversationMessage::select('created_at')
                        ->whereColumn('conversation_messages.conversation_id', 'conversations.id')
                        ->latest()
                        ->take(1),
                    'desc'
                )
                ->paginate($perPage);
            } else {
                $conversations = $query->where('user_id', $user->id)
                    ->orWhereHas('shared_user', function($subQuery) use ($user) {
                        $subQuery->where('user_id', $user->id);
                    })
                    ->with(['user', 'messages' => function ($query) {
                        $query->latest('created_at'); // Order messages by `created_at`
                    }, 'shared_user.user'])
                    ->orderBy(
                        // Order the conversations by the latest message's `created_at`
                        ConversationMessage::select('created_at')
                            ->whereColumn('conversation_messages.conversation_id', 'conversations.id')
                            ->latest()
                            ->take(1),
                        'desc'
                    )
                    ->paginate($perPage);
            }
            

            return response()->json([
                'data' => $conversations->items(),
                'total' => $conversations->total(),
                'current_page' => $conversations->currentPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching conversations', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a Conversation
     *
     * Get details of a specific Conversation.
     *
     * @urlParam id required The ID of the Conversation. Example: 1
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::with(['messages.user', 'messages.prompt', 'shared_user.user', 'user'])->find($id);

            if(!$user->hasRole('Admin')){
                $sharedUsers = $conversation->shared_user()->pluck('user_id')->toArray();
                if(!in_array(Auth::user()->id, $sharedUsers)){
                    if($user->id != $conversation->user_id){
                        return WebApiResponse::error(403, $errors = ['You are not allowed to view this thread'], 'Unauthorized Access!');
                    }
                }

            }
            $response = [
                'message' => 'Data Showed Successfully',
                'data' => $conversation,
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching conversation details', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a new Conversation
     *
     * Create a new Conversation.
     *
     * @bodyParam name string required The name of the Conversation. Example: Basic
     * @bodyParam prompt_id integer required The ID of the prompt. Example: 1
     * @bodyParam message_content string required The content of the initial message. Example: Hello, how can I help you?
     */
    public function createConversation(Request $request)
    {
        set_time_limit(500);
        $validatedData = $request->validate([
            'name' => 'required|string',
            'prompt_id' => 'nullable|exists:prompts,id',
            'message_content' => 'required|string',
        ]);
        $prompt = Prompt::find($request->prompt_id);

        $payload = [
            'prompt' => $validatedData['message_content'],
        ];
        if($prompt){
            $payload['prompt2'] = $prompt->prompt;
        }
        $response = Http::timeout(450)->post(env('AI_APPLICATION_URL').'/conversation/conversation-generate', $payload);
        if (!$response->successful()) {
            return WebApiResponse::error(500, $errors = [], "Can't able to generate the conversation, Please try again.");
        }
        Log::info(['Conversation Generate AI.',$response]);
        $data = $response->json();

        $conversation = Conversation::create([
            'name' => $validatedData['name'],
            'user_id' => auth()->id(),
            'assistantId' => $data['data']['assistantId'],
            'threadId' => $data['data']['threadId'],
        ]);

        $userMessage = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'prompt_id' => $validatedData['prompt_id'] ?? null,
            'user_id' => auth()->id(),
            'role' => 'user',
            'message_content' => $validatedData['message_content'],
        ]);

        $userMessage->load(['prompt']);


        $aiMessage = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'prompt_id' => $validatedData['prompt_id'] ?? null,
            'user_id' => auth()->id(),
            'role' => 'system',
            'message_content' => $data['data']['message'],
        ]);
        $aiMessage->load(['prompt']);

        $data = [
            'conversation' => $conversation,
            'messages' => [$userMessage, $aiMessage]
        ];

        $response = [
            'message' => 'Created Successfully ',
            'data' => $data,
        ];
        return response()->json($response, 201);
    }

    /**
     * Continue a Conversation
     *
     * Continue an existing Conversation.
     *
     * @bodyParam conversation_id integer required The ID of the Conversation. Example: 1
     * @bodyParam prompt_id integer required The ID of the prompt. Example: 2
     * @bodyParam message_content string required The content of the message. Example: How can I assist you further?
     */
    public function continueConversation(Request $request)
    {
        $validatedData = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message_content' => 'nullable|required_without_all:prompt_id|string',
            'prompt_id' => 'nullable|required_without_all:message_content|exists:prompts,id',
        ]);

        $conversation = Conversation::with('messages.user')->find((int) $request->conversation_id);

        $userMessage = ConversationMessage::create([
            'conversation_id' => $validatedData['conversation_id'],
            'prompt_id' => $validatedData['prompt_id'] ?? null,
            'user_id' => auth()->id(),
            'role' => 'user',
            'message_content' => $validatedData['message_content']?? "",
        ]);

        $prompt = Prompt::find($request->prompt_id);
        $payload = [
            'assistantId' => $conversation->assistantId,
            'threadId' => $conversation->threadId,
        ];

        if($prompt){
            $payload['prompt'] = $prompt->prompt;
            if($validatedData['message_content']){
                $payload['prompt2'] = $validatedData['message_content'];
            }
        }else{
            $payload['prompt'] = $validatedData['message_content'];
        }
        $response = Http::timeout(450)->post(env('AI_APPLICATION_URL').'/conversation/conversation-continue', $payload);
        if (!$response->successful()) {
            return WebApiResponse::error(500, $errors = [], "Can't able to generate the message, Please try again.");
        }
        Log::info(['Conversation continue AI.',$response]);
        $data = $response->json();


        $aiMessage = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'prompt_id' => $validatedData['prompt_id'] ?? null,
            'user_id' => auth()->id(),
            'role' => 'system',
            'message_content' => $data['data']['message'],
        ]);

        $data = [
            'conversation' => $conversation,
            'messages' => [$userMessage->load(['user','prompt']), $aiMessage->load(['prompt'])]
        ];

        $response = [
            'message' => 'Created Successfully ',
            'data' => $data,
        ];
        return response()->json($response, 201);
    }

    /**
     * Update a Conversation
     *
     * Update an existing Conversation.
     *
     * @urlParam conversation_id integer required The ID of the Conversation. Example: 1
     * @bodyParam name string required The content of the message. Example: How can I assist you further?
     */

    public function update($id, Request $request){
        $validatedData = $request->validate([
            'name' => 'required|string'
        ]);

        $data = Conversation::findOrFail($id);

        $data->name = $request->name;
        $data->save();

        $response = [
            'message' => 'Updated Successfully ',
            'data' => $data,
        ];
        return response()->json($response, 200);

    }

    /**
     * Update a Conversation Message
     *
     * Update an existing Conversation Message.
     *
     * @urlParam conversation_id integer required The ID of the Conversation. Example: 1
     * @bodyParam name string required The content of the message. Example: How can I assist you further?
     */

    public function updateConversationMessage($id, Request $request){
        $validatedData = $request->validate([
            'message_content' => 'required|string'
        ]);

        $data = ConversationMessage::findOrFail($id);

        $data->message_content = $request->message_content;
        $data->save();

        $response = [
            'message' => 'Update Successfully ',
            'data' => $data->load('user'),
        ];
        return response()->json($response, 200);

    }


    /**
     * Delete a Conversation
     *
     * Delete an existing Conversation.
     *
     * @urlParam conversation_id integer required The ID of the Conversation. Example: 1
     */

    public function delete($id, Request $request){

        $data = Conversation::findOrFail($id);
        $data->messages()->delete();
        $data->delete();

        $response = [
            'message' => 'Deleted Successfully ',
            'data' => [],
        ];
        return response()->json($response, 200);

    }
    /**
     * Share a Conversation
     *
     * Share an existing Conversation.
     *
     * @urlParam conversation_id integer required The ID of the Conversation. Example: 1
     * @bodyParam user_access array required List of User Id to share with. Example: [
     *  [2,1],
     *  [1,2]
     * ]
     */

    public function share($id, Request $request){

        $validatedData = $request->validate([
            'user_access' => 'required|array',
            'user_access.*.user_id' => 'integer|required',
            'user_access.*.access_level' => 'integer|required',
        ]);

        $conversation = Conversation::with('shared_user')->findOrFail($id);

        // $currentSharedUserIds = $conversation->shared_user()->pluck('user_id')->toArray();

        $sharedUsers = $request->user_access;
        foreach ($sharedUsers as $key => $access_detail) {

            // if (in_array($access_detail['user_id'], $currentSharedUserIds)) {
            //     continue;
            // }

            // $conversation->shared_user()->create([
            //     'user_id' => $access_detail['user_id'],
            //     'access_level' => $access_detail['access_level']
            // ]);

            ConversationSharedUser::updateOrCreate(
                [
                    'user_id' => $access_detail['user_id'],
                    'conversation_id' => $conversation->id
                ],
                ['access_level' => $access_detail['access_level']]
            );
        }

        $response = [
            'message' => 'Shared Successfully ',
            'data' => $conversation->load('shared_user.user'),
        ];
        return response()->json($response, 200);

    }
    /**
     * Remove Share a Conversation
     *
     * Remove Share an existing Conversation.
     *
     * @urlParam conversation_id integer required The ID of the Conversation. Example: 1
     * @bodyParam user_id array required List of User Id to share with. Example: [1,2]
     */

    public function removeShare($id, Request $request){

        $validatedData = $request->validate([
            'user_id' => 'required|array',
        ]);

        $conversation = Conversation::findOrFail($id);

        ConversationSharedUser::where('conversation_id', $id)->whereIn('user_id', $request->user_id)->delete();

        $response = [
            'message' => 'Shared Remove Successfully ',
            'data' => [],
        ];
        return response()->json($response, 200);

    }
}
