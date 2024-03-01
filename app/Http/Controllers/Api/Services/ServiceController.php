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
            $query = Services::query();

            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified

            $services = $query->with('projectType')->latest()->paginate($perPage);
            return response()->json([
                'data' => $services->items(),
                'total' => $services->total(),
                'current_page' => $services->currentPage(),
            ]);
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
     *
     *
     */
    public function update(Request $request, $id)
    {
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
     *
     * @response {
     *  "message": "Data fetched successfully"
     * }
     */
    public function serviceTree()
    {
        try {
            $servicesRawData =  Services::with([
                'serviceGroups.serviceScopes.serviceDeliverables.serviceDeliverableTasks' => function ($query) {
                    $query->where('parentTaskId', null)->with('subTasks');
                },
            ])->get();

            $services = [];

            foreach ($servicesRawData as $service) {
                $serviceData = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'groups' => [],
                ];

                foreach ($service->serviceGroups as $group) {
                    $serviceGroupData = [
                        'id' => $group->id,
                        'name' => $group->name,
                        'sows' => [],
                    ];

                    foreach ($group->serviceScopes as $scope) {
                        $scopedata = [
                            'id' => $scope->id,
                            'name' => $scope->name,
                            'deliverables' => [],
                        ];

                        foreach ($scope->serviceDeliverables as $deliverable) {
                            $deliverableData = [
                                'id' => $deliverable->id,
                                'name' => $deliverable->name,
                                'tasks' => [],
                            ];

                            foreach ($deliverable->serviceDeliverableTasks as $task) {
                                $taskData = [
                                    'id' => $task->id,
                                    'name' => $task->name,
                                    'description' => $task->description,
                                    'sub_tasks' => [],
                                ];

                                foreach ($task->subTasks as $subTask) {
                                    $subTaskData = [
                                        'id' => $subTask->id,
                                        'name' => $subTask->name,
                                        'description' => $subTask->description,
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
            return response()->json(['message' => 'Error fetching service', 'error' => $e->getMessage()], 500);
        }
    }
}
