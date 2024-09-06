<?php

namespace App\Http\Controllers\Api;

use App\Models\PromptCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Prompt Categories Management
 * @authenticated
 *
 * APIs for managing Categories Management
 */
class PromptCategoriesController extends Controller
{

    /**
     * Display a listing of Categories Management.
     *
     * @group Prompt Categories Management
     * @queryParam page integer page number.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $categoryQuery = PromptCategory::with(['categoryInfo']);

        if ($request->has('page')) {
            $categoryPagination = $categoryQuery->paginate(10);
            return response()->json([
                'data' => $categoryPagination->items(),
                'total' => $categoryPagination->total(),
                'current_page' => $categoryPagination->currentPage(),
            ]);
        } else {
            $categories = $categoryQuery->get();
            return response()->json([
                'data' => $categories,
                'total' => $categories->count(),
            ]);
        }

    }

    /**
     * Store a new Prompt Categories
     *
     * @group Prompt Categories Management
     *
     * @bodyParam title string required.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|unique:prompt_categories,title',
        ]);

        $category = new PromptCategory;
        $category->title = $validatedData['title'];
        $category->save();

        $category->load(['categoryInfo']);


        $response = [
            'message' => 'Created Successfully ',
            'data' => $category,
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified Categories Management.
     *
     * @group Prompt Categories Management
     *
     * @urlParam Categories Management required The ID of the Categories Management to display. Example: 1
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($id)
    {
        $category = PromptCategory::with(['categoryInfo'])->findOrFail($id);
        $response = [
            'message' => 'View Successfully ',
            'data' => $category,
        ];
        return response()->json($response, 201);
    }

    /**
     * Update the specified Categories Management.
     *
     * @group Prompt Categories Management
     *
     * @urlParam Categories Management required The ID of the prompt category to update. Example: 1
     * @bodyParam title string required.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function update($id, Request $request,)
    {
        $validatedData = $request->validate([
            'title' => 'string|max:255|unique:prompt_categories,title,'.$id,
        ]);
        $category = PromptCategory::with(['categoryInfo'])->findOrFail($id);

        $category->title = $validatedData['title'];
        $category->save();

        $response = [
            'message' => 'Update Successfully ',
            'data' => $category,
        ];
        return response()->json($response, 201);
    }

    /**
     * Remove the specified Categories Management from storage.
     *
     * @group Prompt Categories Management
     *
     * @urlParam Categories Management required The ID of the Categories Management to delete. Example: 1
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($id)
    {
        $category = PromptCategory::findOrFail($id);
        $category->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 204);
    }
}

