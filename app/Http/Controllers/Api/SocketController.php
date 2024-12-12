<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\ChatGptThreadUsing;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationSharedUser;
use App\Models\ProjectSummary;
use App\Models\Prompt;
use App\Models\SocketUserActivity;
use App\Services\OpenAIGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * @group Socket Activity
 * @authenticated
 *
 * APIs for socket activity.
 */
class SocketController extends Controller
{

    /**
     * Socket Activities
     * Get Socket Activities for a document
     * @urlParam id required The ID of the Socket Document. Example: 1
     */
    public function list($document_id)
    {
        try {
            $user = Auth::user();
            $activityList = SocketUserActivity::with(['user'])->where('document_id', $document_id)->where('user_id', $user->id)->get();
            $response = [
                'message' => 'Data Showed Successfully',
                'data' => $activityList,
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching socket activity list', 'error' => $e->getMessage()], 500);
        }
    }
}
