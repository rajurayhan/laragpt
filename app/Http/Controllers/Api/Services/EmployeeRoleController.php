<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRole;
use App\Models\EmployeeRoles;
use Illuminate\Http\Request;

/**
 * @group Employee Roles
 * @authenticated
 *
 * APIs for website services.
 */
class EmployeeRoleController extends Controller
{
    /**
     * Get all Employee Roles
     *
     * Get a list of all employee roles.
     *
     * @queryParam name string Filter by name.
     * @queryParam per_page integer Number of items per page.
     * @queryParam page integer Page number.
     *
     */
    public function index(Request $request)
    {
        try {
            $query = EmployeeRoles::query();

            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified

            $roles = $query->latest()->paginate($perPage);
            return response()->json([
                'data' => $roles->items(),
                'total' => $roles->total(),
                'current_page' => $roles->currentPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching employee roles', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show an Employee Role
     *
     * Get details of a specific employee role.
     *
     * @urlParam id required The ID of the Employee Role. Example: 1
     *
     */
    public function show($id)
    {
        try {
            $role = EmployeeRoles::find($id);
            if (!$role) {
                return response()->json(['message' => 'Employee role not found'], 404);
            }
            return response()->json(['message' => 'Data Showed Successfully', 'data' => $role], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching employee role details', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new Employee Role
     *
     * Create a new Employee Role.
     *
     * @bodyParam name string required The name of the Employee Role. Example: Developer
     * @bodyParam average_hourly decimal required The average hourly rate of the Employee Role. Example: 25.50
     *
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'average_hourly' => 'required|numeric',
        ]);

        try {
            $role = EmployeeRoles::create($validatedData);
            return response()->json(['message' => 'Created Successfully', 'data' => $role], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating employee role', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an Employee Role
     *
     * Update details of a specific employee role.
     *
     * @urlParam id required The ID of the employee role. Example: 1
     *
     * @bodyParam name string required The name of the employee role. Example: Senior Developer
     * @bodyParam average_hourly decimal required The average hourly rate of the Employee Role. Example: 30.00
     *
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'average_hourly' => 'required|numeric',
        ]);

        try {
            $role = EmployeeRoles::findOrFail($id);
            $role->update($validatedData);
            return response()->json(['message' => 'Updated Successfully', 'data' => $role], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating employee role', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete an Employee Role
     *
     * Delete a specific employee role.
     *
     * @urlParam id required The ID of the employee role. Example: 1
     *
     */
    public function destroy($id)
    {
        try {
            $role = EmployeeRoles::findOrFail($id);
            $role->delete();
            return response()->json(['message' => 'Deleted Successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting employee role', 'error' => $e->getMessage()], 500);
        }
    }
}
