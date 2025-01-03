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
     * @queryParam per_page integer Number of records requested per page.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $users = User::with('roles')->orderBy('name', 'ASC')->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $users->items(),
            'total' => $users->total(),
            'current_page' => $users->currentPage(),
        ]);
    }

    /**
     * Update auth user profile
     *
     * @group Users Management
     * @bodyParam name string optional.
     * @bodyParam email string optional.
     * @bodyParam profile_picture string optional.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $userId = auth()->id();
        $user = $request->user();

        $validatedData = $request->validate([
            'email' => 'nullable|email|unique:users,email,'.$userId,
            'name' => 'nullable|string',
            'profile_picture' => 'nullable|string',
        ]);

        $user->update($validatedData);
        $response = [
            'message' => 'Profile update successfully',
            'data' => $user->fresh(),
        ];

        return response()->json($response, 200);
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
    /**
     * Change user password
     *
     * @group Users Management
     * @bodyParam current_password string required.
     * @bodyParam new_password string required.
     * @bodyParam new_password_confirmation string required. Must match the new password.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();
        // Validate input
        $request->validate([
            "current_password" => "required|string",
            "new_password" => "required|string|min:8|confirmed", // Rule confirmed will check for a field named new_password_confirmation
        ]);

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    "message" => "The current password is incorrect."
            ], 400);
        }

        // Update to new password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
                "message" => "Password changed successfully.",
            ], 200);
        }
}

