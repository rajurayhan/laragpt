<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Models\ServiceDeliverableTasks;
use Illuminate\Http\Request;

/**
 * @group Service Deliverable Tasks
 * @authenticated
 *
 * APIs for managing service deliverable tasks.
 */
class ServiceDeliverableTasksController extends Controller
{
    /**
     * Get all Service Deliverable Tasks
     *
     * Get a list of all Service Deliverable Tasks.
     *
     * @queryParam page integer page number.
     * @queryParam serviceDeliverableId integer Service Deliverable Id.
     */
    public function index(Request $request)
    {
        try {
            $query = ServiceDeliverableTasks::query();
            if($request->filled('serviceDeliverableId')){
                $query->where('serviceDeliverableId', $request->serviceDeliverableId);
            }

            $serviceDeliverableTasks = $query->with('serviceDeliverable.serviceScope.serviceGroup.service')->latest()->paginate(10);
            return response()->json([
                'data' => $serviceDeliverableTasks->items(),
                'total' => $serviceDeliverableTasks->total(),
                'current_page' => $serviceDeliverableTasks->currentPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching service deliverable tasks', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a Service Deliverable Task
     *
     * Get details of a specific Service Deliverable Task.
     *
     * @urlParam id required The ID of the Service Deliverable Task. Example: 1
     */
    public function show($id)
    {
        try {
            $serviceDeliverableTask = ServiceDeliverableTasks::find($id);
            $response = [
                'message' => 'Data Showed Successfully',
                'data' => $serviceDeliverableTask
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching service deliverable task details', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new Service Deliverable Task
     *
     * Create a new Service Deliverable Task.
     *
     * @bodyParam name string required The name of the Service Deliverable Task. Example: Design Phase Task
     * @bodyParam description string required The description of the Service Deliverable Task. Example: Design logo
     * @bodyParam cost double required The cost of the Service Deliverable Task. Example: 150.00
     * @bodyParam serviceDeliverableId integer required The ID of the associated service deliverable. Example: 3
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'cost' => 'required|numeric',
            'serviceDeliverableId' => 'required|integer|exists:service_deliverables,id',
        ]);

        $serviceDeliverableTask = ServiceDeliverableTasks::create($validatedData);
        $response = [
            'message' => 'Created Successfully',
            'data' => $serviceDeliverableTask->load('serviceDeliverable.serviceScope.serviceGroup.service')
        ];

        return response()->json($response, 201);
    }

    /**
     * Update a Service Deliverable Task
     *
     * Update details of a specific service deliverable task.
     *
     * @urlParam id required The ID of the service deliverable task. Example: 1
     * @bodyParam name string required The name of the service deliverable task. Example: Updated Design Phase Task
     * @bodyParam description string The description of the service deliverable task. Example: Updated description
     * @bodyParam cost double The cost of the service deliverable task. Example: 200.00
     * @bodyParam serviceDeliverableId integer The ID of the associated service deliverable.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'string',
            'cost' => 'numeric',
            'serviceDeliverableId' => 'integer|exists:service_deliverables,id',
        ]);
        $serviceDeliverableTask = ServiceDeliverableTasks::findOrFail($id);
        $serviceDeliverableTask->update($validatedData);

        $response = [
            'message' => 'Updated Successfully',
            'data' => $serviceDeliverableTask
        ];

        return response()->json($response, 201);
    }

    /**
     * Delete a Service Deliverable Task
     *
     * Delete a specific service deliverable task.
     *
     * @urlParam id required The ID of the service deliverable task. Example: 1
     */
    public function destroy($id)
    {
        try {
            $serviceDeliverableTask = ServiceDeliverableTasks::findOrFail($id);
            $serviceDeliverableTask->delete();
            $response = [
                'message' => 'Deleted Successfully',
                'data' => []
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting service deliverable task', 'error' => $e->getMessage()], 500);
        }
    }
}
