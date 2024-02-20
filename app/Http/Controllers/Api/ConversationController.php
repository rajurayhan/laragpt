<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConversationController extends Controller
{
    public function listConversations()
    {
        // Assuming you want to retrieve all conversations
        $conversations = Conversation::all();

        return response()->json(['conversations' => $conversations]);
    }

    public function createConversation(Request $request)
    {
        // Validate $request
        $this->validate($request, [
            'name' => 'required|string',
            'prompt_id' => 'required|exists:prompts,id',
            'message_content' => 'required|string',
        ]);

        $conversation = Conversation::create([
            'name' => $request->input('name'),
            'user_id' => auth()->id(), // Assuming you are using authentication
        ]);

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'prompt_id' => $request->input('prompt_id'),
            'user_id' => auth()->id(),
            'message_content' => $request->input('message_content'),
        ]);

        // Call OpenAI API and handle the response

        // Insert another row into conversation_messages with the OpenAI API response

        return response()->json(['message' => 'Conversation created successfully']);
    }

    public function continueConversation(Request $request)
    {
        // Validate $request
        $this->validate($request, [
            'conversation_id' => 'required|exists:conversations,id',
            'prompt_id' => 'required|exists:prompts,id',
            'message_content' => 'required|string',
        ]);

        $message = ConversationMessage::create([
            'conversation_id' => $request->input('conversation_id'),
            'prompt_id' => $request->input('prompt_id'),
            'user_id' => auth()->id(),
            'message_content' => $request->input('message_content'),
        ]);

        // Call OpenAI API and handle the response

        // Insert another row into conversation_messages with the OpenAI API response

        return response()->json(['message' => 'Message added to the conversation']);
    }

    // Add the listConversations method
}
