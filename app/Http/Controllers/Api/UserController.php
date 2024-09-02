<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * @group Users Management
 * @authenticated
 *
 * APIs for managing users
 */
class UserController extends Controller
{

    /**
     * Display a listing of users.
     *
     * @group Users Management
     * @queryParam page integer page number.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::with('roles')->paginate(10);

        return response()->json([
            'data' => $users->items(),
            'total' => $users->total(),
            'current_page' => $users->currentPage(),
        ]);
    }

    /**
     * Store a newly created user.
     *
     * @group Users Management
     *
     * @bodyParam email string required The email of the user. Example: "user@example.com."
     * @bodyParam name string required The name of the user. Example: "Example user name."
     * @bodyParam role string required The name of the role. Example: "Admin"
     * @bodyParam password string required The password of the user. Example: "ashska."
     * @bodyParam password_confirmation string required The mathing password of the user. Example: "ashska."
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string',
            'password' => 'required|string|confirmed',
            'role' => 'required|string'
        ]);

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        // Assign role to user
        $role = Role::findByName($request->role, 'sanctum');
        if($role){
            $user->assignRole($role);
        }


        $response = [
            'message' => 'Created Successfully ',
            'data' => $user,
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified user.
     *
     * @group Users Management
     *
     * @urlParam user required The ID of the user to display. Example: 1
     *
     * @param  User $user
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $response = [
            'message' => 'View Successfully ',
            'data' => $user,
        ];
        return response()->json($response, 201);
    }

    /**
     * Update the specified user.
     *
     * @group Users Management
     *
     * @urlParam user required The ID of the user to update. Example: 1
     * @bodyParam email string required The email of the user. Example: "user@example.com."
     * @bodyParam name string required The name of the user. Example: "Example user name."
     * @bodyParam role string required The name of the role. Example: "Admin"
     * @bodyParam password string nullable The password of the user. Example: "ashska."
     * @bodyParam password_confirmation string nullable The mathing password of the user. Example: "ashska."
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  User $user
     * @return \Illuminate\Http\JsonResponse
     */

    public function update($id, Request $request,)
    {
        $validatedData = $request->validate([
            'name' => 'string|max:255',
            'role' => 'required|string',
            'email' => 'email|unique:users,email,' . $id,
            'password' => 'nullable|string|confirmed',
        ]);

        $user = User::findOrFail($request->id);

        $user->name = $request->name;
        $user->email = $request->email;
        if($request->filled('password')){
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $role = Role::findByName($request->role, 'sanctum');
        if($role){
            $user->syncRoles([$role->id]);
        }
        $user->roles;

        $response = [
            'message' => 'Created Successfully ',
            'data' => $user,
        ];
        return response()->json($response, 201);
    }

    /**
     * Remove the specified user from storage.
     *
     * @group Users Management
     *
     * @urlParam user required The ID of the user to delete. Example: 1
     *
     * @param  User $user
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 204);
    }
}

