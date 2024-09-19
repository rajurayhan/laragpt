<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\ProblemsAndGoals;
use App\Models\EmployeeRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


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

    /**
     * Generate Role
     *
     * @group Role
     *
     * @bodyParam problemGoalId int required Id of the Problem Goal ID.
     *
     */
    public function generate(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'problemGoalId' => 'required|int'
            ]);
            set_time_limit(500);

            $problemAndGoal = ProblemsAndGoals::with(['meetingTranscript'])->where('id',$validatedData['problemGoalId'])->first();
            if(!$problemAndGoal){
                return WebApiResponse::error(500, $errors = [], 'Problem and Goal not found.');
            }
            $roles = EmployeeRoles::get();
            $response = Http::timeout(450)->post(env('AI_APPLICATION_URL') . '/estimation/role-generate', [
                'threadId' => $problemAndGoal->meetingTranscript->threadId,
                'assistantId' => $problemAndGoal->meetingTranscript->assistantId,
                'existingRoles' => $roles->map(function ($item, $key) {
                    return [
                        'name'=> $item->name,
                        'averageHourlyRate'=> $item->average_hourly,
                    ];
                })->toArray(),
            ]);

            if (!$response->successful()) {
                return WebApiResponse::error(500, $errors = [], "Can't able to generate role, Please try again.");
            }
            $data = $response->json();
            Log::info(['Role Generate AI.', $data]);

            if (!is_array($data['data']['roles']) || count($data['data']['roles']) < 1 || !isset($data['data']['roles'][0]['name'])) {
                return WebApiResponse::error(500, $errors = [], 'The roles from AI is not expected output, Try again please');
            }
            $roles = $data['data']['roles'];


            DB::beginTransaction();
            foreach($roles as $role){
                $findRole = EmployeeRoles::where('name',$role['name'])->first();
                if(!$findRole){
                    EmployeeRoles::create([
                        'name'=> $role['name'],
                        'average_hourly'=> $role['averageHourlyRate'],
                    ]);
                }
            }
            DB::commit();
            $response = [
                'message' => 'Role generate succcessfully',
                'data' => EmployeeRoles::get()
            ];

            return response()->json($response, 200);

        }catch (\Exception $exception){
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }
}
