<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Models\ServiceGroups;
use Illuminate\Http\Request;

/**
 * @group Service Groups
 *
 * APIs for managing service scopes.
 */
class ServiceGroupController extends Controller
{
    /**
     * Get all Service Groups
     *
     * Get a list of all Service Groups.
     *
     * @queryParam page integer page number.
     * @queryParam serviceId integer Service Id.
     */
    public function index(Request $request)
    {
        try {

            $query = ServiceGroups::query();
            if($request->filled('serviceId')){
                $query->where('serviceId', $request->serviceId);
            }
            $serviceGroups = $query->with('service')->latest()->paginate(10);
            return response()->json([
                'data' => $serviceGroups->items(),
                'total' => $serviceGroups->total(),
                'current_page' => $serviceGroups->currentPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching service scopes', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a Service Group
     *
     * Get details of a specific Service Group.
     *
     * @urlParam id required The ID of the Service Group. Example: 1
     */
    public function show($id)
    {
        try {
            $serviceGroup = ServiceGroups::find($id);
            $response = [
                'message' => 'Data Showed Successfully',
                'data' => $serviceGroup
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching service scope details', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new Service Group
     *
     * Create a new Service Group.
     *
     * @bodyParam name string required The name of the Service Group. Example: Basic
     * @bodyParam serviceId integer required The ID of the associated service. Example: 2
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
            'serviceId' => 'required|integer|exists:services,id',
        ]);

        $serviceGroup = ServiceGroups::create($validatedData);
        $response = [
            'message' => 'Created Successfully',
            'data' => $serviceGroup->load('service')
        ];

        return response()->json($response, 201);

    }

    /**
     * Update a Service Group
     *
     * Update details of a specific service scope.
     *
     * @urlParam id required The ID of the service scope. Example: 1
     * @bodyParam name string required The name of the service scope. Example: Advanced
     * @bodyParam serviceId integer The ID of the associated service.
     */
    public function update(Request $request, $id)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
            'serviceId' => 'required|integer|exists:services,id',
        ]);
        $serviceGroup = ServiceGroups::findOrFail($id);
        $serviceGroup->update($validatedData);

        $response = [
            'message' => 'Updated Successfully',
            'data' => $serviceGroup
        ];

        return response()->json($response, 201);

    }

    /**
     * Delete a Service Group
     *
     * Delete a specific service scope.
     *
     * @urlParam id required The ID of the service scope. Example: 1
     */
    public function destroy($id)
    {
        try {
            $serviceGroup = ServiceGroups::findOrFail($id);
            // $serviceGroup->serviceDeliverables()->delete();
            $serviceGroup->delete();
            $response = [
                'message' => 'Deleted Successfully',
                'data' => []
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting service scope', 'error' => $e->getMessage()], 500);
        }
    }
}
