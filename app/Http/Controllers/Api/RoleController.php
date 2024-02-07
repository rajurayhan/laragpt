<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * @group Roles
     * Retrieve all roles
     *
     * @authenticated
     *
     * @queryParam per_page integer Number of items per page.
     * @queryParam name string Name of Role.
     *
     * @response {
     *   "data": [
     *       {
     *           "id": 1,
     *           "name": "Admin",
     *           "permissions": [
     *               "create post",
     *               "edit post"
     *           ]
     *       },
     *       {
     *           "id": 2,
     *           "name": "Editor",
     *           "permissions": [
     *               "edit post"
     *           ]
     *       }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        $query = Role::query();
        $perPage = $request->input('per_page', 10);
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        $roles = $query->with('permissions')->latest()->paginate($perPage);
        return response()->json([
            'data' => $roles->items(),
            'total' => $roles->total(),
            'current_page' => $roles->currentPage(),
        ]);
    }

    /**
     * @group Roles
     * Create a new role
     *
     * @authenticated
     *
     * @bodyParam name string required The name of the role.
     * @bodyParam permissions array List of permissions assigned to the role.
     *
     * @response {
     *   "id": 1,
     *   "name": "Admin",
     *   "permissions": [
     *       "create post",
     *       "edit post"
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $role = Role::create($request->all());
        $role->syncPermissions($request->input('permissions'));
        $response = [
            'message' => 'Created Successfully',
            'data' => $role,
        ];

        return response()->json($response, 201);
    }

    /**
     * @group Roles
     * Retrieve a specific role
     *
     * @authenticated
     *
     * @urlParam role_id int required The ID of the role.
     *
     * @response {
     *   "id": 1,
     *   "name": "Admin",
     *   "permissions": [
     *       "create post",
     *       "edit post"
     *   ]
     * }
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $response = [
            'message' => 'Data Showed Successfully',
            'data' => $role
        ];
        return response()->json($response, 201);
    }

    /**
     * @group Roles
     * Update a role
     *
     * @authenticated
     *
     * @urlParam role_id int required The ID of the role.
     * @bodyParam name string required The name of the role.
     * @bodyParam permissions array List of permissions assigned to the role.
     *
     * @response {
     *   "id": 1,
     *   "name": "Admin",
     *   "permissions": [
     *       "create post",
     *       "edit post"
     *   ]
     * }
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->update($request->all());
        $role->syncPermissions($request->input('permissions'));
        $response = [
            'message' => 'Updated Successfully',
            'data' => $role
        ];

        return response()->json($response, 201);
    }

    /**
     * @group Roles
     * Delete a role
     *
     * @authenticated
     *
     * @urlParam role_id int required The ID of the role.
     *
     * @response 204
     */
    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 204);
    }
}
