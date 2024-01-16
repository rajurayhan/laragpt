<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Models\Services;
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
     * @queryParam page integer page number.
     *
     */
    public function index()
    {
        try {
            $services = Services::latest()->paginate(10);
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
            $service = Services::find($id);
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
     *
     *
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);

        $service = Services::create($validatedData);
        $response = [
            'message' => 'Created Successfully',
            'data' => $service
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
     *
     *
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);
        $service = Services::findOrfail($id);
        $service->update($validatedData);

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
}
