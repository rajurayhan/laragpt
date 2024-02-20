<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Http\Request;
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

            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified

            $conversations = $query->latest()->paginate($perPage);

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
            $conversation = Conversation::find($id);
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
        $validatedData = $request->validate([
            'name' => 'required|string',
            'prompt_id' => 'required|exists:prompts,id',
            'message_content' => 'required|string',
        ]);

        $conversation = Conversation::create([
            'name' => $validatedData['name'],
            'user_id' => auth()->id(),
        ]);

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'prompt_id' => $validatedData['prompt_id'],
            'user_id' => auth()->id(),
            'message_content' => $validatedData['message_content'],
        ]);

        // Call OpenAI API and handle the response

        // Insert another row into conversation_messages with the OpenAI API response

        return response()->json(['message' => 'Conversation created successfully']);
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
            'prompt_id' => 'required|exists:prompts,id',
            'message_content' => 'required|string',
        ]);

        $message = ConversationMessage::create([
            'conversation_id' => $validatedData['conversation_id'],
            'prompt_id' => $validatedData['prompt_id'],
            'user_id' => auth()->id(),
            'message_content' => $validatedData['message_content'],
        ]);

        // Call OpenAI API and handle the response

        // Insert another row into conversation_messages with the OpenAI API response

        return response()->json(['message' => 'Message added to the conversation']);
    }
}
