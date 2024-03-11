<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Models\ServiceDeliverables;
use App\Services\ModelOrderManagerService;
use Illuminate\Http\Request;

/**
 * @group Service Deliverables
 * @authenticated
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
     * @queryParam serviceGroupId integer Service Group Id.
     * @queryParam serviceId integer Service Id.
     * @queryParam name string Filter by name.
     * @queryParam per_page integer Number of items per page.
     */
    public function index(Request $request)
    {
        try {
            $query = ServiceDeliverables::query();
            $query->with('serviceScope.serviceGroup.service');

            if ($request->filled('serviceScopeId')) {
                $query->where('serviceScopeId', $request->input('serviceScopeId'));
            }

            if ($request->filled('serviceGroupId')) {
                $query->whereHas('serviceScope.serviceGroup', function ($groupQuery) use ($request) {
                    $groupQuery->where('id', $request->input('serviceGroupId'));
                });
            }

            if ($request->filled('serviceId')) {
                $query->whereHas('serviceScope.serviceGroup.service', function ($serviceQuery) use ($request) {
                    $serviceQuery->where('id', $request->input('serviceId'));
                });
            }

            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified
            $serviceDeliverables = $query->latest()->paginate($perPage);

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
     * @bodyParam deliverables array required An array of groups for the Service Group. Each group should have 'name' and 'order'. Example: [{"name": "Basic", "order": 1}, {"name": "Standard", "order": 2}]

     * @bodyParam serviceScopeId integer required The ID of the associated service scope. Example: 3
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'deliverables' => 'required|array', 
            'deliverables.*.name' => 'required|string',
            'deliverables.*.order' => 'required|integer',
            'serviceScopeId' => 'required|integer|exists:service_scopes,id',
        ]);

        $serviceDeliverables = [];

        foreach ($validatedData['deliverables'] as $deliverable) {
            $data = [
                'name' => $deliverable['name'],
                'order' => $deliverable['order'],
                'serviceScopeId' => $validatedData['serviceScopeId'],
            ];

            $orderManager = new ModelOrderManagerService(ServiceDeliverables::class);
            $serviceDeliverable = $orderManager->addOrUpdateItem($data); 

            $serviceDeliverables[] = $serviceDeliverable->load('serviceScope.serviceGroup.service');
        }

        $response = [
            'message' => 'Created Successfully',
            'data' => $serviceDeliverables,
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
     * @bodyParam order integer required The order of the service deliverables. Example: 1
     * @bodyParam serviceScopeId integer The ID of the associated service scope.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'order' => 'required|integer',
            'serviceScopeId' => 'integer|exists:service_scopes,id',
        ]);
        $serviceDeliverable = ServiceDeliverables::findOrFail($id);
        $orderManager = new ModelOrderManagerService(ServiceDeliverables::class);
        $serviceDeliverable = $orderManager->addOrUpdateItem($validatedData, $id); 

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
