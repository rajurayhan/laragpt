<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\Models\MeetingType;
use Illuminate\Http\Request;


/**
 * @group MeetingType
 * @authenticated
 *
 * APIs for website meetingType.
 */
class MeetingTypeController extends Controller
{

    /**
     * Get all MeetingType
     *
     * Get a list of all MeetingType.
     *
     * @queryParam page integer page number.
     *
     */
    public function index()
    {
        try {
            $meetingType = MeetingType::latest()->paginate(10);
            return response()->json([
                'data' => $meetingType->items(),
                'total' => $meetingType->total(),
                'current_page' => $meetingType->currentPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching meetingType', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a MeetingType
     *
     * Get details of a specific MeetingType.
     *
     * @urlParam id required The ID of the MeetingType. Example: 1
     *
     */
    public function show($id)
    {
        try {
            $meetingType = MeetingType::find($id);
            $response = [
                'message' => 'Data Showed Successfully',
                'data' => $meetingType
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching meetingType details', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new MeetingType
     *
     * Create a new MeetingType.
     *
     * @bodyParam name string required The name of the MeetingType. Example: Header
     *
     *
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);

        $meetingType = MeetingType::create($validatedData);
        $response = [
            'message' => 'Created Successfully',
            'data' => $meetingType
        ];

        return response()->json($response, 201);

    }

    /**
     * Update a website component category
     *
     * Update details of a specific meetingType.
     *
     * @urlParam id required The ID of the meetingType. Example: 1
     *
     * @bodyParam name string required The name of the meetingType. Example: Updated Header
     *
     *
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);
        $meetingType = MeetingType::findOrfail($id);
        $meetingType->update($validatedData);

        $response = [
            'message' => 'Created Successfully',
            'data' => $meetingType
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
            $meetingType = MeetingType::findOrfail($id); 
            $meetingType->delete();
            $response = [
                'message' => 'Deleted Successfully',
                'data' => []
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting meetingType', 'error' => $e->getMessage()], 500);
        }
    }
}
