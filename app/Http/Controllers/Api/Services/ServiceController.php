<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Services\ModelOrderManagerService;
use Illuminate\Http\Request;


/**
 * @group Services
 * @authenticated
 *
 * APIs for website services.
 */
class ServiceController extends Controller
{

    /**
     * Get all Services
     *
     * Get a list of all Services.
     *
     * @queryParam name string Filter by name.
     * @queryParam per_page integer Number of items per page.
     * @queryParam page integer page number.
     *
     */
    public function index(Request $request)
    {
        try {
            $query = Services::query()->with('projectType')->latest();

            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            if ($request->has('page')) {
                $services = $query->paginate($request->get('per_page')??10);
                return response()->json([
                    'data' => $services->items(),
                    'total' => $services->total(),
                    'current_page' => $services->currentPage(),
                    'per_page' => $services->perPage(),
                ]);
            }else{
                return response()->json([
                    'data' => $query->get(),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching services', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a Service
     *
     * Get details of a specific Service.
     *
     * @urlParam id required The ID of the Service. Example: 1
     *
     */
    public function show($id)
    {
        try {
            $service = Services::with('projectType')->find($id);
            $response = [
                'message' => 'Data Showed Successfully',
                'data' => $service
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching service details', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new Service
     *
     * Create a new Service.
     *
     * @bodyParam name string required The name of the Service. Example: Header
     * @bodyParam projectTypeId integer required The type of the project.
     * @bodyParam order  integer required Data Order
     *
     *
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
            'projectTypeId' => 'required|integer|exists:project_types,id',
            'order' => 'required|integer',
        ]);

        // $service = Services::create($validatedData);
        $orderManager = new ModelOrderManagerService(Services::class);
        $service = $orderManager->addOrUpdateItem($validatedData);
        $response = [
            'message' => 'Created Successfully',
            'data' => $service->load('projectType')
        ];

        return response()->json($response, 201);

    }

    /**
     * Update a website component category
     *
     * Update details of a specific service.
     *
     * @urlParam id required The ID of the service. Example: 1
     *
     * @bodyParam name string required The name of the service. Example: Updated Header
     * @bodyParam projectTypeId integer required The type of the project.
     * @bodyParam order  integer required Data Order
     *
     *
     */
    public function update(Request $request, $id)
    {
        // return $id;
        $validatedData = $request->validate([
            'name' => 'required|string',
            'projectTypeId' => 'required|integer|exists:project_types,id',
            'order' => 'required|integer',
        ]);
        // $service = Services::findOrfail($id);
        // $service->update($validatedData);
        $orderManager = new ModelOrderManagerService(Services::class);
        $service = $orderManager->addOrUpdateItem($validatedData, $id);
        $response = [
            'message' => 'Created Successfully',
            'data' => $service
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
            $service = Services::findOrfail($id);
            // $service->serviceScopes()->delete();
            $service->delete();
            $response = [
                'message' => 'Deleted Successfully',
                'data' => []
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting service', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Service Data Tree
     *
     * Send data as a tree structure
     * @queryParam projectTypeId integer projectTypeId filter.
     *
     * @response {
     *  "message": "Data fetched successfully"
     * }
     */
    public function serviceTree(Request $request)
    {
        try {
            $query = Services::query();

            if($request->projectTypeId){
                $query->where('projectTypeId', $request->projectTypeId);
            }
            $servicesRawData =  $query->with([
                'projectType',
                'serviceGroups' => function ($query) {
                    $query->orderBy('order');
                },
                'serviceGroups.serviceScopes' => function ($query) {
                    $query->orderBy('order');
                },
                'serviceGroups.serviceScopes.serviceDeliverables' => function ($query) {
                    $query->orderBy('order');
                },
                'serviceGroups.serviceScopes.serviceDeliverables.serviceDeliverableTasks' => function ($query) {
                    $query->where('parentTaskId', null)->orderBy('order')->with('subTasks.employeeRole', 'employeeRole');
                },
            ])->orderBy('order')->get();
            // $servicesRawData =  $query->with([
            //     'projectType',
            //     'serviceGroups.serviceScopes.serviceDeliverables.serviceDeliverableTasks' => function ($query) {
            //         $query->where('parentTaskId', null)->with('subTasks');
            //     },
            // ])->orderBy('order')->get();

            $services = [];

            foreach ($servicesRawData as $service) {
                $serviceData = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'order' => $service->order,
                    'groups' => [],
                    'projectType' => $service->projectType ? [
                        'id' => $service->projectType->id,
                        'name' => $service->projectType->name,
                    ] : null,
                ];

                foreach ($service->serviceGroups as $group) {
                    $serviceGroupData = [
                        'id' => $group->id,
                        'serviceId' => $service->id,
                        'name' => $group->name,
                        'order' => $group->order,
                        'sows' => [],
                    ];

                    foreach ($group->serviceScopes as $scope) {
                        $scopedata = [
                            'id' => $scope->id,
                            'groupId' => $group->id,
                            'name' => $scope->name,
                            'order' => $scope->order,
                            'deliverables' => [],
                        ];

                        foreach ($scope->serviceDeliverables as $deliverable) {
                            $deliverableData = [
                                'id' => $deliverable->id,
                                'scopeId' => $scope->id,
                                'name' => $deliverable->name,
                                'order' => $deliverable->order,
                                'tasks' => [],
                            ];

                            foreach ($deliverable->serviceDeliverableTasks as $task) {
                                $taskEmployeeRole = null;
                                if(isset($task->employeeRole)){
                                    $taskEmployeeRole = [
                                        'id' => $task->employeeRole->id,
                                        'name' => $task->employeeRole->name,
                                        'average_hourly' => $task->employeeRole->average_hourly,
                                    ];
                                }
                                $taskData = [
                                    'id' => $task->id,
                                    'deliverableId' => $deliverable->id,
                                    'name' => $task->name,
                                    'order' => $task->order,
                                    'cost' => $task->cost,
                                    'description' => $task->description,
                                    'employeeRole' => $taskEmployeeRole,
                                    'sub_tasks' => [],
                                ];

                                foreach ($task->subTasks as $subTask) {
                                    $subTaskEmployeeRole = null;
                                    if(isset($subTask->employeeRole)){
                                        $subTaskEmployeeRole = [
                                            'id' => $subTask->employeeRole->id,
                                            'name' => $subTask->employeeRole->name,
                                            'average_hourly' => $subTask->employeeRole->average_hourly,
                                        ];
                                    }
                                    $subTaskData = [
                                        'id' => $subTask->id,
                                        'taskId' => $task->id,
                                        'deliverableId' => $deliverable->id,
                                        'name' => $subTask->name,
                                        'order' => $subTask->order,
                                        'cost' => $task->cost,
                                        'description' => $subTask->description,
                                        'employeeRole' => $subTaskEmployeeRole,
                                    ];

                                    $taskData['sub_tasks'][] = $subTaskData;
                                }

                                $deliverableData['tasks'][] = $taskData;
                            }

                            $scopedata['deliverables'][] = $deliverableData;
                        }

                        $serviceGroupData['sows'][] = $scopedata;
                    }

                    $serviceData['groups'][] = $serviceGroupData;
                }

                $services[] = $serviceData;
            }
            $response = [
                'message' => 'Data fetched successfully',
                'data' => ['services' => $services]
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            \Log::info($e);
            return response()->json(['message' => 'Error fetching service', 'error' => $e->getMessage()], 500);
        }
    }
}
