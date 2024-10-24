<?php

namespace App\Http\Controllers\Api;

use App\Models\Memory;
use App\Models\Prompt;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Memory Management
 * @authenticated
 *
 * APIs for managing memory
 */
class MemoryController extends Controller
{

    /**
     * Display a listing of Memory.
     *
     * @group Memory Management
     * @queryParam page integer page number.
     * @queryParam promptId integer page number.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $memoryQuery = Memory::query();

        if ($request->get('promptId')) {
            $memoryQuery->whereRaw('JSON_CONTAINS(promptIds, ?)', [$request->get('promptId')] );
        }
        if ($request->get('title')) {
            $memoryQuery->where('title', 'like', '%' . $request->input('title') . '%');
        }

        if ($request->has('page')) {
            $memoriesPagination = $memoryQuery->paginate(10);
            $data = collect( $memoriesPagination->items() );
            $promptList = Prompt::whereIn('id', $data->pluck('promptIds')->flatten())->get()->keyBy('id');
            $memories = $this->promptRelations( $data , $promptList);
            return response()->json([
                'data' => $memories,
                'total' => $memoriesPagination->total(),
                'current_page' => $memoriesPagination->currentPage(),
            ]);
        } else {
            $memories = $memoryQuery->get();
            $data = collect( collect( $memories ) );
            $promptList = Prompt::whereIn('id', $data->pluck('promptIds')->flatten())->get()->keyBy('id');
            $memories = $this->promptRelations($data, $promptList);
            return response()->json([
                'data' => $memories,
                'total' => $memories->count(),
            ]);
        }

    }

    function promptRelations($memories, $promptList){
        return $memories->map(function ($memory) use($promptList) {
            if(empty($memory->promptIds)){
                $memory['promptRelations'] = [];
                return $memory;
            };
            $promptRelations = array_map(function($promptId) use ($promptList){
                return !empty($promptList[$promptId])? $promptList[$promptId]: null;
            },$memory->promptIds);
            $memory['promptRelations'] = array_filter($promptRelations);
            return $memory;
        });
    }

    /**
     * Store a new Memory
     *
     * @group Memory Management
     *
     * @bodyParam title string required.
     * @bodyParam prompt string required.
     * @bodyParam promptIds int[] required An array of prompt. Example: [1,2,3]
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'prompt' => 'required|string',
            'promptIds' => 'required|array',
        ]);

        $memory = new Memory;
        $memory->title = $validatedData['title'];
        $memory->prompt = $validatedData['prompt'];
        $memory->promptIds = $validatedData['promptIds'];
        $memory->save();

        $promptList = Prompt::whereIn('id', collect($validatedData['promptIds'])->flatten())->get()->keyBy('id');
        $memories = $this->promptRelations( collect([$memory]) , $promptList);


        $response = [
            'message' => 'Created Successfully ',
            'data' => $memories[0],
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified memory.
     *
     * @group Memory Management
     *
     * @urlParam memory required The ID of the memory to display. Example: 1
     *
     * @param  Memory $memory
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($id)
    {
        $memory = Memory::findOrFail($id);
        $response = [
            'message' => 'View Successfully ',
            'data' => $memory,
        ];
        return response()->json($response, 201);
    }

    /**
     * Update the specified memory.
     *
     * @group Memory Management
     *
     * @urlParam memory required The ID of the memory to update. Example: 1
     * @bodyParam title string required.
     * @bodyParam prompt string required.
     * @bodyParam promptIds int[] required An array of prompt. Example: [1,2,3]
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Memory $memory
     * @return \Illuminate\Http\JsonResponse
     */

    public function update($id, Request $request,)
    {
        $validatedData = $request->validate([
            'title' => 'string|required|max:255',
            'prompt' => 'string|required',
            'promptIds' => 'required|array',
        ]);
        $memory = Memory::findOrFail($id);

        $memory->title = $validatedData['title'];
        $memory->prompt = $validatedData['prompt'];
        $memory->promptIds = $validatedData['promptIds'];
        $memory->save();

        $response = [
            'message' => 'Update Successfully ',
            'data' => $memory,
        ];
        return response()->json($response, 201);
    }

    /**
     * Remove the specified memory from storage.
     *
     * @group Memory Management
     *
     * @urlParam memory required The ID of the memory to delete. Example: 1
     * @param  Memory $memory
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($id)
    {
        $memory = Memory::findOrFail($id);
        $memory->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 204);
    }
}

