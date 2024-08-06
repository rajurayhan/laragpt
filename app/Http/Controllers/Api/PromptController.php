<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Models\Prompt;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $prompts = Prompt::orderBy('type','ASC')->orderBy('serial','ASC')->paginate(10);

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
     * @bodyParam prompt string required The content of the prompt. Example: "Example prompt content."
     * @bodyParam name string required The name of the prompt. Example: "Example prompt name."
     * @bodyParam serial int not required. Example: 1
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
            'serial' => 'integer',
        ]);


        $prompt = Prompt::create($validatedData);

        $response = [
            'message' => 'Created Successfully',
            'data' => $prompt,
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
        $prompt = Prompt::findOrFail($id);
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
     * @bodyParam prompt string required The content of the prompt. Example: "Updated prompt content."
     * @bodyParam name string required The content of the name. Example: "Updated prompt name."
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
            'serial' => 'required|integer',
        ]);

        $prompt = Prompt::findOrFail($id);
        $prompt->update($validatedData);
        $response = [
            'message' => 'Update Successfully ',
            'data' => $prompt,
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
}

