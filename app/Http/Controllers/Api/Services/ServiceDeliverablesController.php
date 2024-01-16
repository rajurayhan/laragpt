<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Models\ServiceDeliverables;
use Illuminate\Http\Request;

/**
 * @group Service Deliverables
 *
 * APIs for managing service deliverables.
 */
class ServiceDeliverablesController extends Controller
{
    /**
     * Get all Service Deliverables
     *
     * Get a list of all Service Deliverables.
     *
     * @queryParam page integer page number.
     * @queryParam serviceScopeId integer Service Scope Id.
     */
    public function index(Request $request)
    {
        try {
            $query = ServiceDeliverables::query();
            if($request->filled('serviceScopeId')){
                $query->where('serviceScopeId', $request->serviceScopeId);
            }
            $serviceDeliverables = $query->with('serviceScope.serviceGroup.service')->latest()->paginate(10);
            return response()->json([
                'data' => $serviceDeliverables->items(),
                'total' => $serviceDeliverables->total(),
                'current_page' => $serviceDeliverables->currentPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching service deliverables', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a Service Deliverable
     *
     * Get details of a specific Service Deliverable.
     *
     * @urlParam id required The ID of the Service Deliverable. Example: 1
     */
    public function show($id)
    {
        try {
            $serviceDeliverable = ServiceDeliverables::find($id);
            $response = [
                'message' => 'Data Showed Successfully',
                'data' => $serviceDeliverable
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching service deliverable details', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new Service Deliverable
     *
     * Create a new Service Deliverable.
     *
     * @bodyParam name string required The name of the Service Deliverable. Example: Design Phase
     * @bodyParam serviceScopeId integer required The ID of the associated service scope. Example: 3
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'serviceScopeId' => 'required|integer|exists:service_scopes,id',
        ]);

        $serviceDeliverable = ServiceDeliverables::create($validatedData);
        $response = [
            'message' => 'Created Successfully',
            'data' => $serviceDeliverable->load('serviceScope.service')
        ];

        return response()->json($response, 201);
    }

    /**
     * Update a Service Deliverable
     *
     * Update details of a specific service deliverable.
     *
     * @urlParam id required The ID of the service deliverable. Example: 1
     * @bodyParam name string required The name of the service deliverable. Example: Implementation Phase
     * @bodyParam serviceScopeId integer The ID of the associated service scope.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'serviceScopeId' => 'integer|exists:service_scopes,id',
        ]);
        $serviceDeliverable = ServiceDeliverables::findOrFail($id);
        $serviceDeliverable->update($validatedData);

        $response = [
            'message' => 'Updated Successfully',
            'data' => $serviceDeliverable
        ];

        return response()->json($response, 201);
    }

    /**
     * Delete a Service Deliverable
     *
     * Delete a specific service deliverable.
     *
     * @urlParam id required The ID of the service deliverable. Example: 1
     */
    public function destroy($id)
    {
        try {
            $serviceDeliverable = ServiceDeliverables::findOrFail($id);
            $serviceDeliverable->delete();
            $response = [
                'message' => 'Deleted Successfully',
                'data' => []
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting service deliverable', 'error' => $e->getMessage()], 500);
        }
    }
}
