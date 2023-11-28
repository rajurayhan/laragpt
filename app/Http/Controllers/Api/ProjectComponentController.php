<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectComponent;
use App\Http\Resources\ProjectComponentResource;
use App\Http\Requests\CreateProjectComponentRequest;
use App\Http\Requests\UpdateProjectComponentRequest;

/**
 * @group Project Components
 *
 * APIs for managing project components.
 */

class ProjectComponentController extends Controller
{
    /**
     * Get all project components
     *
     * Get a list of all project components.
     *
     * @response {
     *  "data": [
     *      {
     *          "project_id": 1,
     *          "component_id": 1,
     *          "quantity": 3,
     *          "created_at": "2023-07-01T12:00:00Z",
     *          "updated_at": "2023-07-01T12:30:00Z"
     *      },
     *      {
     *          "project_id": 1,
     *          "component_id": 2,
     *          "quantity": 2,
     *          "created_at": "2023-07-02T09:00:00Z",
     *          "updated_at": "2023-07-02T09:30:00Z"
     *      }
     *  ]
     * }
     */
    public function index()
    {
        try {
            $projectComponents = ProjectComponent::all();
            return ProjectComponentResource::collection($projectComponents);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching project components', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a project component
     *
     * Get details of a specific project component grouped by category_id.
     *
     * @queryParam project_id required The ID of the project. Example: 1
     * @queryParam component_id required The ID of the website component. Example: 1
     *
     * @response {
     *     "data": {
     *         "category_id_1": {
     *             "category_name": "Category A",
     *             "total_cost": 1500,
     *             "components": [
     *                 {
     *                     "project_id": 1,
     *                     "component_id": 1,
     *                     "component_name": "Component X",
     *                     "quantity": 3,
     *                     "total_component_cost": 900,
     *                     "created_at": "2023-07-01T12:00:00Z",
     *                     "updated_at": "2023-07-01T12:30:00Z"
     *                 },
     *                 {
     *                     "project_id": 1,
     *                     "component_id": 2,
     *                     "component_name": "Component Y",
     *                     "quantity": 2,
     *                     "total_component_cost": 600,
     *                     "created_at": "2023-07-02T09:00:00Z",
     *                     "updated_at": "2023-07-02T09:30:00Z"
     *                 }
     *             ]
     *         },
     *         "category_id_2": {
     *             "category_name": "Category B",
     *             "total_cost": 500,
     *             "components": [
     *                 {
     *                     "project_id": 1,
     *                     "component_id": 3,
     *                     "component_name": "Component Z",
     *                     "quantity": 1,
     *                     "total_component_cost": 500,
     *                     "created_at": "2023-07-03T10:00:00Z",
     *                     "updated_at": "2023-07-03T10:30:00Z"
     *                 }
     *             ]
     *         }
     *     },
     *     "grand_total": 2000
     * }
     */
    public function show($projectId, $componentId)
    {
        try {
            $projectComponents = ProjectComponent::where('project_id', $projectId)
                ->where('component_id', $componentId)
                ->with('component.category') // Eager load the component relationship with its category
                ->get();

            // Group project components by their category_id and calculate total cost
            $groupedComponents = [];
            $grandTotal = 0;
            foreach ($projectComponents as $projectComponent) {
                $categoryKey = 'category_id_' . $projectComponent->component->category_id;
                $groupedComponents[$categoryKey]['category_name'] = $projectComponent->component->category->category_name;
                $groupedComponents[$categoryKey]['components'][] = [
                    'project_id' => $projectComponent->project_id,
                    'component_id' => $projectComponent->component_id,
                    'component_name' => $projectComponent->component->component_name,
                    'quantity' => $projectComponent->quantity,
                    'total_component_cost' => $projectComponent->total_component_cost,
                    'created_at' => $projectComponent->created_at,
                    'updated_at' => $projectComponent->updated_at,
                ];
                $groupedComponents[$categoryKey]['total_cost'] = ($groupedComponents[$categoryKey]['total_cost'] ?? 0) + $projectComponent->total_component_cost;
                $grandTotal += $projectComponent->total_component_cost;
            }

            return response()->json(['data' => $groupedComponents, 'grand_total' => $grandTotal]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching project components', 'error' => $e->getMessage()], 500);
        }
    }




    /**
     * Add components to a project
     *
     * Add website components to a specific project.
     *
     * @bodyParam project_id int required The ID of the project. Example: 1
     * @bodyParam components array required An array of component details.
     * @bodyParam components.*.component_id int required The ID of the website component. Example: 1
     * @bodyParam components.*.quantity int required The quantity of the website component. Example: 3
     *
     * @response {
     *  "data": [
     *      {
     *          "project_id": 1,
     *          "component_id": 1,
     *          "quantity": 3,
     *          "created_at": "2023-07-01T12:00:00Z",
     *          "updated_at": "2023-07-01T12:30:00Z"
     *      },
     *      {
     *          "project_id": 1,
     *          "component_id": 2,
     *          "quantity": 2,
     *          "created_at": "2023-07-02T09:00:00Z",
     *          "updated_at": "2023-07-02T09:30:00Z"
     *      }
     *  ]
     * }
     */
    public function store(CreateProjectComponentRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $components = $validatedData['components'];

            // Loop through the components array and create project components
            $projectComponents = [];
            foreach ($components as $component) {
                $projectComponent = ProjectComponent::create([
                    'project_id' => $validatedData['project_id'],
                    'component_id' => $component['component_id'],
                    'quantity' => $component['quantity'],
                ]);
                $projectComponents[] = $projectComponent;
            }

            return ProjectComponentResource::collection($projectComponents);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error adding components to project', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update components of a project
     *
     * Update details of website components in a specific project.
     *
     * @bodyParam project_id int required The ID of the project. Example: 1
     * @bodyParam components array required An array of component details.
     * @bodyParam components.*.component_id int required The ID of the website component. Example: 1
     * @bodyParam components.*.quantity int required The quantity of the website component. Example: 5
     *
     * @response {
     *  "data": [
     *      {
     *          "project_id": 1,
     *          "component_id": 1,
     *          "quantity": 5,
     *          "created_at": "2023-07-01T12:00:00Z",
     *          "updated_at": "2023-07-03T09:30:00Z"
     *      },
     *      {
     *          "project_id": 1,
     *          "component_id": 2,
     *          "quantity": 3,
     *          "created_at": "2023-07-02T09:00:00Z",
     *          "updated_at": "2023-07-03T09:30:00Z"
     *      }
     *  ]
     * }
     */
    public function update(UpdateProjectComponentRequest $request, $projectId)
    {
        try {
            $validatedData = $request->validated();
            $components = $validatedData['components'];

            // Loop through the components array and update project components
            $projectComponents = [];
            foreach ($components as $component) {
                $projectComponent = ProjectComponent::where('project_id', $projectId)
                    ->where('component_id', $component['component_id'])
                    ->first();

                if ($projectComponent) {
                    $projectComponent->update(['quantity' => $component['quantity']]);
                    $projectComponents[] = $projectComponent;
                }
            }

            return ProjectComponentResource::collection($projectComponents);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating project components', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a project component
     *
     * Delete a specific project component.
     *
     * @queryParam project_id required The ID of the project. Example: 1
     * @queryParam component_id required The ID of the website component. Example: 1
     *
     * @response {
     *  "message": "Project component deleted successfully"
     * }
     */
    public function destroy($projectId, $componentId)
    {
        try {
            $projectComponent = ProjectComponent::where('project_id', $projectId)->where('component_id', $componentId)->first();
            $projectComponent->delete();
            return response()->json(['message' => 'Project component deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting project component', 'error' => $e->getMessage()], 500);
        }
    }
}
