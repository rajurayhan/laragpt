<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;

/**
 * @group Projects
 * @authenticated
 *
 * APIs for managing projects.
 */

class ProjectController extends Controller
{
    /**
     * Get all projects
     *
     * Get a list of all projects.
     *
     * @response {
     *  "data": [
     *      {
     *          "project_id": 1,
     *          "project_name": "Project A",
     *          "project_description": "This is project A",
     *          "total_cost": "1000.00",
     *          "created_at": "2023-07-01T12:00:00Z",
     *          "updated_at": "2023-07-01T12:30:00Z"
     *      },
     *      {
     *          "project_id": 2,
     *          "project_name": "Project B",
     *          "project_description": "This is project B",
     *          "total_cost": "2000.00",
     *          "created_at": "2023-07-02T09:00:00Z",
     *          "updated_at": "2023-07-02T09:30:00Z"
     *      }
     *  ]
     * }
     */
    public function index()
    {
        try {
            $projects = Project::all();
            return ProjectResource::collection($projects);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching projects', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a project
     *
     * Get details of a specific project.
     *
     * @queryParam project_id required The ID of the project. Example: 1
     *
     * @response {
     *  "data": {
     *      "project_id": 1,
     *      "project_name": "Project A",
     *      "project_description": "This is project A",
     *      "total_cost": "1000.00",
     *      "created_at": "2023-07-01T12:00:00Z",
     *      "updated_at": "2023-07-01T12:30:00Z"
     *  }
     * }
     */
    public function show(Project $project)
    {
        try {
            return new ProjectResource($project);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching project', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new project
     *
     * Create a new project.
     *
     * @bodyParam project_name string required The name of the project. Example: Project C
     * @bodyParam project_description string required The description of the project. Example: This is project C
     * @bodyParam total_cost numeric required The total cost of the project. Example: 3000.00
     *
     * @response {
     *  "data": {
     *      "project_id": 3,
     *      "project_name": "Project C",
     *      "project_description": "This is project C",
     *      "total_cost": "3000.00",
     *      "created_at": "2023-07-03T09:00:00Z",
     *      "updated_at": "2023-07-03T09:30:00Z"
     *  }
     * }
     */
    public function store(CreateProjectRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $project = Project::create($validatedData);
            return new ProjectResource($project);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating project', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a project
     *
     * Update details of a specific project.
     *
     * @queryParam project_id required The ID of the project. Example: 1
     *
     * @bodyParam project_name string required The name of the project. Example: Updated Project A
     * @bodyParam project_description string required The description of the project. Example: Updated project A description
     * @bodyParam total_cost numeric required The total cost of the project. Example: 1200.00
     *
     * @response {
     *  "data": {
     *      "project_id": 1,
     *      "project_name": "Updated Project A",
     *      "project_description": "Updated project A description",
     *      "total_cost": "1200.00",
     *      "created_at": "2023-07-01T12:00:00Z",
     *      "updated_at": "2023-07-03T09:30:00Z"
     *  }
     * }
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        try {
            $validatedData = $request->validated();
            $project->update($validatedData);
            return new ProjectResource($project);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating project', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a project
     *
     * Delete a specific project.
     *
     * @queryParam project_id required The ID of the project. Example: 1
     *
     * @response {
     *  "message": "Project deleted successfully"
     * }
     */
    public function destroy(Project $project)
    {
        try {
            $project->delete();
            return response()->json(['message' => 'Project deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting project', 'error' => $e->getMessage()], 500);
        }
    }
}
