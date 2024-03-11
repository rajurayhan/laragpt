<?php 

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpdateLog;
use Illuminate\Http\Request;

/**
 * @group Update Logs
 * @authenticated
 *
 * APIs for update logs.
 */
class UpdateLogController extends Controller
{
    /**
     * Get all Update Logs
     *
     * Get a list of all Update Logs.
     *
     * @queryParam per_page integer Number of items per page.
     * @queryParam page integer Page number.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $updateLogs = UpdateLog::latest()->paginate($perPage);

            return response()->json([
                'data' => $updateLogs->items(),
                'total' => $updateLogs->total(),
                'current_page' => $updateLogs->currentPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching update logs', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show an Update Log
     *
     * Get details of a specific Update Log.
     *
     * @urlParam id required The ID of the Update Log. Example: 1
     */
    public function show($id)
    {
        try {
            $updateLog = UpdateLog::findOrFail($id);

            return response()->json([
                'message' => 'Data Showed Successfully',
                'data' => $updateLog,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching update log details', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new Update Log
     *
     * Create a new Update Log.
     *
     * @bodyParam date date required The date of the update. Example: 2024-03-11
     * @bodyParam deployed longtext required Description of the deployed changes.
     * @bodyParam next longtext required Description of the upcoming changes.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'deployed' => 'required|string',
            'next' => 'required|string',
        ]);

        $updateLog = UpdateLog::create($validatedData);

        return response()->json([
            'message' => 'Created Successfully',
            'data' => $updateLog,
        ], 201);
    }

    /**
     * Update an Update Log
     *
     * Update details of a specific Update Log.
     *
     * @urlParam id required The ID of the Update Log. Example: 1
     *
     * @bodyParam date date required The date of the update. Example: 2024-03-11
     * @bodyParam deployed longtext required Description of the deployed changes.
     * @bodyParam next longtext required Description of the upcoming changes.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'deployed' => 'required|string',
            'next' => 'required|string',
        ]);

        $updateLog = UpdateLog::findOrFail($id);
        $updateLog->update($validatedData);

        return response()->json([
            'message' => 'Updated Successfully',
            'data' => $updateLog,
        ], 200);
    }

    /**
     * Delete an Update Log
     *
     * Delete a specific Update Log.
     *
     * @urlParam id required The ID of the Update Log. Example: 1
     *
     * @response {
     *  "message": "Update Log deleted successfully"
     * }
     */
    public function destroy($id)
    {
        try {
            $updateLog = UpdateLog::findOrFail($id);
            $updateLog->delete();

            return response()->json(['message' => 'Deleted Successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting update log', 'error' => $e->getMessage()], 500);
        }
    }
}
