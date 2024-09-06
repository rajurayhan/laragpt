<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Models\Prompt;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PromptSharedUser;

/**
 * @group Prompts Management
 * @authenticated
 *
 * APIs for managing prompts
 */
class PromptController extends Controller
{

    /**
     * Display a listing of prompts.
     *
     * @group Prompts Management
     * @queryParam page integer page number.
     * @queryParam category_id integer Category id.
     * @queryParam name string prompt name.
     * @queryParam prompt string Prompt description
     * @queryParam type integer Prompt Type.
     * @queryParam per_page integer Number of items per page.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Prompt::query();
        if($request->filled('name')){
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if($request->filled('prompt')){
            $query->where('prompt', 'like', '%' . $request->input('prompt') . '%');
        }
        if($request->filled('type')){
            $query->where('type', $request->input('type'));
        }
        if($request->filled('category_id')){
            $query->where('category_id', $request->input('category_id'));
        }

        $prompts = $query->orderBy('name','ASC')->paginate($request->get('per_page')??10);

        return response()->json([
            'data' => $prompts->items(),
            'total' => $prompts->total(),
            'current_page' => $prompts->currentPage(),
        ]);
    }

    /**
     * Store a newly created prompt.
     *
     * @group Prompts Management
     *
     * @bodyParam type integer required The type of the prompt (corresponding to PromptType Enum values). Example: 1
     * @bodyParam category_id integer required The type of the prompt category. Example: 1
     * @bodyParam prompt string required The content of the prompt. Example: "Example prompt content."
     * @bodyParam name string required The name of the prompt. Example: "Example prompt name."
     * @bodyParam action_type string required The action tpe of the prompt. Example: "input-only | expected-output"
     * @bodyParam serial int not required. Example: 1
     * @bodyParam user_id array not required List of user this propt can see in hive assistant. Example: [1,2]
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required|integer|in:' . implode(',', PromptType::getValues()),
            'prompt' => 'required|string',
            'name' => 'required|string',
            'action_type' => 'required|string',
            'serial' => 'integer',
            'category_id' => 'integer|nullable',
            'user_id' => 'array|nullable',
        ]);


        $prompt = Prompt::create(collect($validatedData)->except('user_id')->toArray());

        if(is_array($request->user_id)){
            foreach ($request->user_id as $key => $user_id) {
                PromptSharedUser::create(
                    [
                        'prompt_id' => $prompt->id,
                        'user_id' => $user_id
                    ]
                );
            }
        }

        $response = [
            'message' => 'Created Successfully',
            'data' => $prompt->load('shared_user.user'),
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified prompt.
     *
     * @group Prompts Management
     *
     * @urlParam prompt required The ID of the prompt to display. Example: 1
     *
     * @param  Prompt $prompt
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($id)
    {
        $prompt = Prompt::with('shared_user.user')->findOrFail($id);
        $response = [
            'message' => 'View Successfully ',
            'data' => $prompt,
        ];
        return response()->json($response, 201);
    }

    /**
     * Update the specified prompt.
     *
     * @group Prompts Management
     *
     * @urlParam prompt required The ID of the prompt to update. Example: 1
     * @bodyParam type integer required The type of the prompt (corresponding to PromptType Enum values). Example: 2
     * @bodyParam category_id integer required The type of the prompt category. Example: 1
     * @bodyParam prompt string required The content of the prompt. Example: "Updated prompt content."
     * @bodyParam action_type string required The action tpe of the prompt. Example: "input-only | expected-output"
     * @bodyParam name string required The content of the name. Example: "Updated prompt name."
     * @bodyParam user_id array not required List of user this propt can see in hive assistant. Example: [1,2]
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Prompt $prompt
     * @return \Illuminate\Http\JsonResponse
     */

    public function update($id, Request $request,)
    {
        $validatedData = $request->validate([
            'type' => 'required|integer|in:' . implode(',', PromptType::getValues()),
            'prompt' => 'required|string',
            'name' => 'required|string',
            'action_type' => 'required|string',
            'serial' => 'required|integer',
            'user_id' => 'array|nullable',
            'category_id' => 'integer|nullable',
        ]);

        $prompt = Prompt::findOrFail($id);
        $prompt->update($validatedData);

        if(is_array($request->user_id)){
            $this->syncPromptShareUsers($prompt, $request->user_id);
        }

        $response = [
            'message' => 'Update Successfully ',
            'data' => $prompt->load('shared_user.user'),
        ];
        return response()->json($response, 201);
    }

    /**
     * Remove the specified prompt from storage.
     *
     * @group Prompts Management
     *
     * @urlParam prompt required The ID of the prompt to delete. Example: 1
     *
     * @param  Prompt $prompt
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($id)
    {
        $prompt = Prompt::findOrFail($id);
        $prompt->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 204);
    }


    private function syncPromptShareUsers(Prompt $prompt, array $newUserIds)
    {
        // Get all current user IDs for the prompt
        $currentUserIds = $prompt->shared_user()->pluck('user_id')->toArray();

        // Find the users to delete
        $usersToDelete = array_diff($currentUserIds, $newUserIds);

        // Delete the users that are no longer present
        PromptSharedUser::where('prompt_id', $prompt->id)
                        ->whereIn('user_id', $usersToDelete)
                        ->delete();

        // Loop through the new user IDs
        foreach ($newUserIds as $userId) {
            // If the user already exists in the prompt, skip it
            if (in_array($userId, $currentUserIds)) {
                continue;
            }

            // If the user doesn't exist, create a new record
            $prompt->shared_user()->create([
                'user_id' => $userId,
            ]);
        }
    }
}

