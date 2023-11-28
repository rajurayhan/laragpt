<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WebsiteComponent;
use App\Http\Resources\WebsiteComponentResource;
use App\Http\Requests\CreateWebsiteComponentRequest;
use App\Http\Requests\UpdateWebsiteComponentRequest;

/**
 * @group Website Components
 *
 * APIs for managing website components.
 */

class WebsiteComponentController extends Controller
{
    /**
     * Get all website components
     *
     * Get a list of all website components.
     *
     * @response {
     *  "data": [
     *      {
     *          "component_id": 1,
     *          "component_name": "Logo",
     *          "category_id": 1,
     *          "component_description": "Company logo for header",
     *          "component_cost": "50.00",
     *          "category": {
     *              "category_id": 1,
     *              "category_name": "Header",
     *              "created_at": "2023-07-01T12:00:00Z",
     *              "updated_at": "2023-07-01T12:30:00Z"
     *          },
     *          "created_at": "2023-07-01T12:00:00Z",
     *          "updated_at": "2023-07-01T12:30:00Z"
     *      },
     *      {
     *          "component_id": 2,
     *          "component_name": "Contact Form",
     *          "category_id": 1,
     *          "component_description": "Contact form for footer",
     *          "component_cost": "100.00",
     *          "category": {
     *              "category_id": 1,
     *              "category_name": "Footer",
     *              "created_at": "2023-07-02T09:00:00Z",
     *              "updated_at": "2023-07-02T09:30:00Z"
     *          },
     *          "created_at": "2023-07-02T09:00:00Z",
     *          "updated_at": "2023-07-02T09:30:00Z"
     *      }
     *  ]
     * }
     */
    public function index()
    {
        try {
            $components = WebsiteComponent::with('category')->get();
            return WebsiteComponentResource::collection($components);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching website components', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a website component
     *
     * Get details of a specific website component.
     *
     * @queryParam component_id required The ID of the website component. Example: 1
     *
     * @response {
     *  "data": {
     *      "component_id": 1,
     *      "component_name": "Logo",
     *      "category_id": 1,
     *      "component_description": "Company logo for header",
     *      "component_cost": "50.00",
     *      "category": {
     *          "category_id": 1,
     *          "category_name": "Header",
     *          "created_at": "2023-07-01T12:00:00Z",
     *          "updated_at": "2023-07-01T12:30:00Z"
     *      },
     *      "created_at": "2023-07-01T12:00:00Z",
     *      "updated_at": "2023-07-01T12:30:00Z"
     *  }
     * }
     */
    public function show(WebsiteComponent $component)
    {
        try {
            return new WebsiteComponentResource($component);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching website component', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new website component
     *
     * Create a new website component.
     *
     * @bodyParam component_name string required The name of the website component. Example: Logo
     * @bodyParam category_id int required The ID of the website component category. Example: 1
     * @bodyParam component_description string required The description of the website component. Example: Company logo for header
     * @bodyParam component_cost numeric required The cost of the website component. Example: 50.00
     *
     * @response {
     *  "data": {
     *      "component_id": 3,
     *      "component_name": "Banner",
     *      "category_id": 1,
     *      "component_description": "Banner image for header",
     *      "component_cost": "80.00",
     *      "category": {
     *          "category_id": 1,
     *          "category_name": "Header",
     *          "created_at": "2023-07-01T12:00:00Z",
     *          "updated_at": "2023-07-01T12:30:00Z"
     *      },
     *      "created_at": "2023-07-03T09:00:00Z",
     *      "updated_at": "2023-07-03T09:30:00Z"
     *  }
     * }
     */
    public function store(CreateWebsiteComponentRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $component = WebsiteComponent::create($validatedData);
            return new WebsiteComponentResource($component);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating website component', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a website component
     *
     * Update details of a specific website component.
     *
     * @queryParam component_id required The ID of the website component. Example: 1
     *
     * @bodyParam component_name string required The name of the website component. Example: Updated Logo
     * @bodyParam category_id int required The ID of the website component category. Example: 1
     * @bodyParam component_description string required The description of the website component. Example: Updated company logo for header
     * @bodyParam component_cost numeric required The cost of the website component. Example: 60.00
     *
     * @response {
     *  "data": {
     *      "component_id": 1,
     *      "component_name": "Updated Logo",
     *      "category_id": 1,
     *      "component_description": "Updated company logo for header",
     *      "component_cost": "60.00",
     *      "category": {
     *          "category_id": 1,
     *          "category_name": "Header",
     *          "created_at": "2023-07-01T12:00:00Z",
     *          "updated_at": "2023-07-01T12:30:00Z"
     *      },
     *      "created_at": "2023-07-01T12:00:00Z",
     *      "updated_at": "2023-07-03T09:30:00Z"
     *  }
     * }
     */
    public function update(UpdateWebsiteComponentRequest $request, WebsiteComponent $component)
    {
        try {
            $validatedData = $request->validated();
            $component->update($validatedData);
            return new WebsiteComponentResource($component);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating website component', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a website component
     *
     * Delete a specific website component.
     *
     * @queryParam component_id required The ID of the website component. Example: 1
     *
     * @response {
     *  "message": "Website component deleted successfully"
     * }
     */
    public function destroy(WebsiteComponent $component)
    {
        try {
            $component->delete();
            return response()->json(['message' => 'Website component deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting website component', 'error' => $e->getMessage()], 500);
        }
    }
}
