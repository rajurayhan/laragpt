<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\Models\ProjectType;
use Illuminate\Http\Request;


/**
 * @group ProjectType
 * @authenticated
 *
 * APIs for website projectType.
 */
class ProjectTypeController extends Controller
{

    /**
     * Get all ProjectType
     *
     * Get a list of all ProjectType.
     *
     * @queryParam page integer page number.
     *
     */
    public function index()
    {
        try {
            $projectType = ProjectType::latest()->paginate(10);
            return response()->json([
                'data' => $projectType->items(),
                'total' => $projectType->total(),
                'current_page' => $projectType->currentPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching projectType', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a ProjectType
     *
     * Get details of a specific ProjectType.
     *
     * @urlParam id required The ID of the ProjectType. Example: 1
     *
     */
    public function show($id)
    {
        try {
            $projectType = ProjectType::find($id);
            $response = [
                'message' => 'Data Showed Successfully',
                'data' => $projectType
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching projectType details', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new ProjectType
     *
     * Create a new ProjectType.
     *
     * @bodyParam name string required The name of the ProjectType. Example: Header
     *
     *
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);

        $projectType = ProjectType::create($validatedData);
        $response = [
            'message' => 'Created Successfully',
            'data' => $projectType
        ];

        return response()->json($response, 201);

    }

    /**
     * Update a website component category
     *
     * Update details of a specific projectType.
     *
     * @urlParam id required The ID of the projectType. Example: 1
     *
     * @bodyParam name string required The name of the projectType. Example: Updated Header
     *
     *
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);
        $projectType = ProjectType::findOrfail($id);
        $projectType->update($validatedData);

        $response = [
            'message' => 'Created Successfully',
            'data' => $projectType
        ];

        return response()->json($response, 201);
    }

    /**
     * Delete a website component category
     *
     * Delete a specific website component category.
     *
     * @urlParam id required The ID of the website component category. Example: 1
     *
     * @response {
     *  "message": "Website component category deleted successfully"
     * }
     */
    public function destroy($id)
    {
        try {
            $projectType = ProjectType::findOrfail($id); 
            $projectType->delete();
            $response = [
                'message' => 'Deleted Successfully',
                'data' => []
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting projectType', 'error' => $e->getMessage()], 500);
        }
    }
}
