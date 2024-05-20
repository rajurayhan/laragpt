<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Models\ServiceGroups;
use App\Models\Services;
use App\Services\ModelOrderManagerService;
use Illuminate\Http\Request;

/**
 * @group Service Groups
 * @authenticated
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
     * @queryParam name string Filter by name.
     * @queryParam serviceId integer Service Id.
     * @queryParam per_page integer Number of items per page.
     */
    public function index(Request $request)
    {
        try {

            $query = ServiceGroups::query();
            if($request->filled('serviceId')){
                $query->where('serviceId', $request->serviceId);
            }

            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }
            $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified

            $serviceGroups = $query->with('service')->latest()->paginate($perPage);
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
     * @bodyParam groups array required An array of groups for the Service Group. Each group should have 'name' and 'order'. Example: [{"name": "Basic", "order": 1}, {"name": "Standard", "order": 2}]
     * @bodyParam groups[].name string required The name of the task.
     * @bodyParam groups[].order integer required The order of the task.
     * @bodyParam serviceId integer required The ID of the associated service. Example: 2
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'groups' => 'required|array',
            'groups.*.name' => 'required|string',
            'groups.*.order' => 'required|integer',
            'serviceId' => 'required|integer|exists:services,id',
        ]);

        $findService = Services::findOrFail($validatedData['serviceId']);

        $serviceGroups = [];

        foreach ($validatedData['groups'] as $group) {
            $data = [
                'name' => $group['name'],
                'order' => $group['order'],
                'serviceId' => $validatedData['serviceId'],
                'projectTypeId'=> $findService->projectTypeId,
            ];

            // return $data;

            $orderManager = new ModelOrderManagerService(ServiceGroups::class);
            $serviceGroup = $orderManager->addOrUpdateItem($data, null,'serviceId', $validatedData['serviceId']);
            $serviceGroups[] = $serviceGroup->load('service');
        }

        $response = [
            'message' => 'Created Successfully',
            'data' => $serviceGroups,
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
     * @bodyParam order integer required The order of the service group. Example: 1
     * @bodyParam serviceId integer The ID of the associated service.
     */
    public function update(Request $request, $id)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
            'order' => 'required|integer',
            'serviceId' => 'required|integer|exists:services,id',
        ]);

        $findService = Services::findOrFail($validatedData['serviceId']);
        $orderManager = new ModelOrderManagerService(ServiceGroups::class);
        $serviceGroup = $orderManager->addOrUpdateItem(array_merge($validatedData, ['projectTypeId'=> $findService->projectTypeId,]), $id, 'serviceId', $validatedData['serviceId']);
        // $serviceGroup = ServiceGroups::findOrFail($id);
        // $serviceGroup->update($validatedData);

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
