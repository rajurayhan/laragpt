<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WebsiteComponentCategory;
use App\Http\Resources\WebsiteComponentCategoryResource;
use App\Http\Requests\CreateWebsiteComponentCategoryRequest;
use App\Http\Requests\UpdateWebsiteComponentCategoryRequest;

/**
 * @group Website Component Categories
 *
 * APIs for managing website component categories.
 */

class WebsiteComponentCategoryController extends Controller
{
    /**
     * Get all website component categories
     *
     * Get a list of all website component categories.
     *
     * @response {
     *  "data": [
     *      {
     *          "category_id": 1,
     *          "category_name": "Header",
     *          "created_at": "2023-07-01T12:00:00Z",
     *          "updated_at": "2023-07-01T12:30:00Z"
     *      },
     *      {
     *          "category_id": 2,
     *          "category_name": "Footer",
     *          "created_at": "2023-07-02T09:00:00Z",
     *          "updated_at": "2023-07-02T09:30:00Z"
     *      }
     *  ]
     * }
     */
    public function index()
    {
        try {
            $categories = WebsiteComponentCategory::all();
            return WebsiteComponentCategoryResource::collection($categories);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching website component categories', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a website component category
     *
     * Get details of a specific website component category.
     *
     * @queryParam category_id required The ID of the website component category. Example: 1
     *
     * @response {
     *  "data": {
     *      "category_id": 1,
     *      "category_name": "Header",
     *      "created_at": "2023-07-01T12:00:00Z",
     *      "updated_at": "2023-07-01T12:30:00Z"
     *  }
     * }
     */
    public function show(WebsiteComponentCategory $category)
    {
        try {
            return new WebsiteComponentCategoryResource($category);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching website component category', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new website component category
     *
     * Create a new website component category.
     *
     * @bodyParam category_name string required The name of the website component category. Example: Header
     *
     * @response {
     *  "data": {
     *      "category_id": 3,
     *      "category_name": "Sidebar",
     *      "created_at": "2023-07-03T09:00:00Z",
     *      "updated_at": "2023-07-03T09:30:00Z"
     *  }
     * }
     */
    public function store(CreateWebsiteComponentCategoryRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $category = WebsiteComponentCategory::create($validatedData);
            return new WebsiteComponentCategoryResource($category);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating website component category', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a website component category
     *
     * Update details of a specific website component category.
     *
     * @queryParam category_id required The ID of the website component category. Example: 1
     *
     * @bodyParam category_name string required The name of the website component category. Example: Updated Header
     *
     * @response {
     *  "data": {
     *      "category_id": 1,
     *      "category_name": "Updated Header",
     *      "created_at": "2023-07-01T12:00:00Z",
     *      "updated_at": "2023-07-03T09:30:00Z"
     *  }
     * }
     */
    public function update(UpdateWebsiteComponentCategoryRequest $request, WebsiteComponentCategory $category)
    {
        try {
            $validatedData = $request->validated();
            $category->update($validatedData);
            return new WebsiteComponentCategoryResource($category);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating website component category', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a website component category
     *
     * Delete a specific website component category.
     *
     * @queryParam category_id required The ID of the website component category. Example: 1
     *
     * @response {
     *  "message": "Website component category deleted successfully"
     * }
     */
    public function destroy(WebsiteComponentCategory $category)
    {
        try {
            $category->delete();
            return response()->json(['message' => 'Website component category deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting website component category', 'error' => $e->getMessage()], 500);
        }
    }
}
