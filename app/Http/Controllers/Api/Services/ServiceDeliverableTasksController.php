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
     * @queryParam serviceDeliverableId integer Service Deliverable Id.
     * @queryParam serviceScopeId integer Service Scope Id.
     * @queryParam serviceGroupId integer Service Group Id.
     * @queryParam serviceId integer Service Id.
     * @queryParam name string Filter by name.
     * @queryParam page integer page number.
     * @queryParam per_page integer Number of items per page.
     * @response {
     *     "data": [
     *         {
     *             "id": 1,
     *             "name": "Task 1",
     *             ...
     *         },
     *         {
     *             "id": 2,
     *             "name": "Task 2",
     *             ...
     *         },
     *         ...
     *     ],
     *     "total": 25,
     *     "current_page": 1
     * }
     * @response 500 {
     *     "message": "Error fetching service deliverable tasks",
     *     "error": "Error message details"
     * }
     */
    public function index(Request $request)
    {
        try {
            $query = ServiceDeliverableTasks::query();
            $query->with('serviceDeliverable.serviceScope.serviceGroup.service');

            // Apply filters
            if ($request->filled('serviceDeliverableId')) {
                $query->where('serviceDeliverableId', $request->input('serviceDeliverableId'));
            }

            if ($request->filled('serviceScopeId')) {
                $query->whereHas('serviceDeliverable.serviceScope', function ($scopeQuery) use ($request) {
                    $scopeQuery->where('id', $request->input('serviceScopeId'));
                });
            }

            if ($request->filled('serviceGroupId')) {
                $query->whereHas('serviceDeliverable.serviceScope.serviceGroup', function ($groupQuery) use ($request) {
                    $groupQuery->where('id', $request->input('serviceGroupId'));
                });
            }

            if ($request->filled('serviceId')) {
                $query->whereHas('serviceDeliverable.serviceScope.serviceGroup.service', function ($serviceQuery) use ($request) {
                    $serviceQuery->where('id', $request->input('serviceId'));
                });
            }

            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            $perPage = $request->input('per_page', 10);
            $serviceDeliverableTasks = $query->latest()->paginate($perPage);

            // return response()->json([
            //     'data' => $serviceDeliverableTasks,
            // ]);

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

    // /**
    //  * Store a new Service Deliverable Task
    //  *
    //  * Create a new Service Deliverable Task.
    //  *
    //  * @bodyParam name string required The name of the Service Deliverable Task. Example: Design Phase Task
    //  * @bodyParam description string required The description of the Service Deliverable Task. Example: Design logo
    //  * @bodyParam cost double required The cost of the Service Deliverable Task. Example: 150.00
    //  * @bodyParam serviceDeliverableId integer required The ID of the associated service deliverable. Example: 3
    //  */
    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'name' => 'required|string',
    //         'description' => 'required|string',
    //         'cost' => 'required|numeric',
    //         'serviceDeliverableId' => 'required|integer|exists:service_deliverables,id',
    //     ]);

    //     $serviceDeliverableTask = ServiceDeliverableTasks::create($validatedData);
    //     $response = [
    //         'message' => 'Created Successfully',
    //         'data' => $serviceDeliverableTask->load('serviceDeliverable.serviceScope.serviceGroup.service')
    //     ];

    //     return response()->json($response, 201);
    // }

    /**
     * Store Service Deliverable Tasks
     *
     * Store multiple Service Deliverable Tasks against a single serviceDeliverableId.
     *
     * @bodyParam tasks array required An array of tasks to be created.
     * @bodyParam tasks[].name string required The name of the task.
     * @bodyParam tasks[].description string required The description of the task.
     * @bodyParam tasks[].cost numeric required The cost of the task.
     * @bodyParam serviceDeliverableId integer required The ID of the service deliverable.
     * @bodyParam parentTaskId integer nullable The ID of the parent task.
     *
     * @response {
     *     "message": "Tasks created successfully",
     *     "data": [
     *         {
     *             "id": 1,
     *             "name": "Task1",
     *             "description": "Description1",
     *             "cost": 10,
     *             "serviceDeliverableId": 1,
     *             "created_at": "2024-02-09T00:00:00.000000Z",
     *             "updated_at": "2024-02-09T00:00:00.000000Z"
     *         },
     *         {
     *             "id": 2,
     *             "name": "Task2",
     *             "description": "Description2",
     *             "cost": 15,
     *             "serviceDeliverableId": 1,
     *             "created_at": "2024-02-09T00:00:00.000000Z",
     *             "updated_at": "2024-02-09T00:00:00.000000Z"
     *         }
     *     ]
     * }
     * @response 422 {
     *     "message": "The given data was invalid.",
     *     "errors": {
     *         "tasks.0.name": [
     *             "The tasks.0.name field is required."
     *         ],
     *         "tasks.0.description": [
     *             "The tasks.0.description field is required."
     *         ]
     *     }
     * }
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            '*.name' => 'required|string',
            '*.description' => 'required|string',
            '*.cost' => 'required|numeric',
            'serviceDeliverableId' => 'required|integer|exists:service_deliverables,id',
            'parentTaskId' => 'nullable|integer|exists:service_deliverable_tasks,id',
        ]);

        $tasks = [];

        foreach ($validatedData as $data) {
            $task = ServiceDeliverableTasks::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'cost' => $data['cost'],
                'serviceDeliverableId' => $request->input('serviceDeliverableId'),
                'parentTaskId' => $data['parentTaskId'],
            ]);

            $tasks[] = $task;
        }

        $response = [
            'message' => 'Tasks created successfully',
            'data' => $tasks,
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
