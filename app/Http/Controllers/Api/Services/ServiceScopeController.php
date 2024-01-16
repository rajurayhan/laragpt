<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Models\ServiceScopes;
use Illuminate\Http\Request;

/**
 * @group Service Scopes
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
     */
    public function index(Request $request)
    {
        try {

            $query = ServiceScopes::query();
            if($request->filled('serviceGroupId')){
                $query->where('serviceGroupId', $request->serviceGroupId);
            }
            $serviceScopes = $query->with('serviceGroup.service')->latest()->paginate(10);
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
     * @bodyParam name string required The name of the Service Scope. Example: Basic
     * @bodyParam serviceGroupId integer required The ID of the associated service. Example: 2
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
            'serviceGroupId' => 'required|integer|exists:service_groups,id',
        ]);

        $serviceScope = ServiceScopes::create($validatedData);
        $response = [
            'message' => 'Created Successfully',
            'data' => $serviceScope->load('serviceGroup.service')
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
     * @bodyParam serviceGroupId integer The ID of the associated service.
     */
    public function update(Request $request, $id)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
            'serviceGroupId' => 'required|integer|exists:service_groups,id',
        ]);
        $serviceScope = ServiceScopes::findOrFail($id);
        $serviceScope->update($validatedData);

        $response = [
            'message' => 'Updated Successfully',
            'data' => $serviceScope
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
