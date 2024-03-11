<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Models\ServiceScopes;
use App\Services\ModelOrderManagerService;
use Illuminate\Http\Request;

/**
 * @group Service Scopes
 * @authenticated
 *
 * APIs for managing service scopes.
 */
class ServiceScopeController extends Controller
{
    /**
     * Get all Service Scopes
     *
     * Get a list of all Service Scopes.
     *
     * @queryParam page integer page number.
     * @queryParam serviceGroupId integer Service group Id.
     * @queryParam serviceId integer Service Id.
     * @queryParam name string Filter by name.
     * @queryParam per_page integer Number of items per page.
     * @response {
     *     "data": [
     *         {
     *             "id": 1,
     *             "name": "Scope 1",
     *             ...
     *         },
     *         {
     *             "id": 2,
     *             "name": "Scope 2",
     *             ...
     *         },
     *         ...
     *     ],
     *     "total": 25,
     *     "current_page": 1
     * }
     * @response 500 {
     *     "message": "Error fetching service scopes",
     *     "error": "Error message details"
     * }
     */
    public function index(Request $request)
    {
        try {
            $query = ServiceScopes::query();

            if ($request->filled('serviceGroupId')) {
                $query->where('serviceGroupId', $request->input('serviceGroupId'));
            }

            if ($request->filled('serviceId')) {
                $query->whereHas('serviceGroup.service', function ($subQuery) use ($request) {
                    $subQuery->where('id', $request->input('serviceId'));
                });
            }

            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified
            $serviceScopes = $query->with('serviceGroup.service')->latest()->paginate($perPage);

            return response()->json([
                'data' => $serviceScopes->items(),
                'total' => $serviceScopes->total(),
                'current_page' => $serviceScopes->currentPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching service scopes', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a Service Scope
     *
     * Get details of a specific Service Scope.
     *
     * @urlParam id required The ID of the Service Scope. Example: 1
     */
    public function show($id)
    {
        try {
            $serviceScope = ServiceScopes::find($id);
            $response = [
                'message' => 'Data Showed Successfully',
                'data' => $serviceScope
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching service scope details', 'error' => $e->getMessage()], 500);
        }
    } 

    /**
     * Store a new Service Scope
     *
     * Create a new Service Scope.
     *
     * @bodyParam scopes array required An array of groups for the Service Group. Each group should have 'name' and 'order'. Example: [{"name": "Basic", "order": 1}, {"name": "Standard", "order": 2}]
     * @bodyParam scopes[].name string required The name of the task.
     * @bodyParam scopes[].order integer required The order of the task.
     * @bodyParam serviceGroupId integer required The ID of the associated service group. Example: 2
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'scopes' => 'required|array', 
            'scopes.*.name' => 'required|string',
            'scopes.*.order' => 'required|integer',
            'serviceGroupId' => 'required|integer|exists:service_groups,id',
        ]);

        $serviceScopes = [];

        foreach ($validatedData['scopes'] as $scope) {
            $data = [
                'name' => $scope['name'],
                'order' => $scope['order'],
                'serviceGroupId' => $validatedData['serviceGroupId'],
            ];

            $orderManager = new ModelOrderManagerService(ServiceScopes::class);
            $serviceScope = $orderManager->addOrUpdateItem($data); 
            $serviceScopes[] = $serviceScope->load('serviceGroup.service');
        }

        $response = [
            'message' => 'Created Successfully',
            'data' => $serviceScopes,
        ];

        return response()->json($response, 201);
    }


    /**
     * Update a Service Scope
     *
     * Update details of a specific service scope.
     *
     * @urlParam id required The ID of the service scope. Example: 1
     * @bodyParam name string required The name of the service scope. Example: Advanced
     * @bodyParam order integer required The order of the service scope. Example: 1
     * @bodyParam serviceGroupId integer The ID of the associated service.
     */
    public function update(Request $request, $id)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
            'order' => 'required|integer',
            'serviceGroupId' => 'required|integer|exists:service_groups,id',
        ]); 
        $orderManager = new ModelOrderManagerService(ServiceScopes::class);
        $serviceScope = $orderManager->addOrUpdateItem($validatedData, $id);

        $response = [
            'message' => 'Updated Successfully',
            'data' => $serviceScope->load('serviceGroup.service')
        ];

        return response()->json($response, 201);

    }

    /**
     * Delete a Service Scope
     *
     * Delete a specific service scope.
     *
     * @urlParam id required The ID of the service scope. Example: 1
     */
    public function destroy($id)
    {
        try {
            $serviceScope = ServiceScopes::findOrFail($id);
            // $serviceScope->serviceDeliverables()->delete();
            $serviceScope->delete();
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
