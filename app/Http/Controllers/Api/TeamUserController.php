<?php

namespace App\Http\Controllers\Api;

use App\Libraries\WebApiResponse;
use App\Models\PromptSharedTeam;
use App\Models\TeamUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Team User Management
 * @authenticated
 *
 * APIs for managing team
 */
class TeamUserController extends Controller
{

    /**
     * Display a listing of Team User.
     *
     * @group Team User Management
     * @queryParam page integer page number.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $teamQuery = TeamUser::with(['team','user']);

        if ($request->has('page')) {
            $teamPagination = $teamQuery->paginate(10);
            return response()->json([
                'data' => $teamPagination->items(),
                'total' => $teamPagination->total(),
                'current_page' => $teamPagination->currentPage(),
            ]);
        } else {
            $team = $teamQuery->get();
            return response()->json([
                'data' => $team,
                'total' => $team->count(),
            ]);
        }

    }

    /**
     * Store a new Team user
     *
     * @group Team User Management
     *
     * @bodyParam teamId string int of the Team.
     * @bodyParam userId string int of the User.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'teamId' => 'required|int',
            'userId' => 'required|int',
        ]);

        $teamUser = new TeamUser();
        $teamUser->teamId = $validatedData['teamId'];
        $teamUser->userId = $validatedData['userId'];
        $teamUser->save();

        $teamUser->load(['team','user']);


        $response = [
            'message' => 'Created Successfully',
            'data' => $teamUser,
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified team.
     *
     * @group Team User Management
     *
     * @urlParam id required. The ID of the team user to display. Example: 1
     *
     * @param  TeamUser $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($id)
    {
        $team = TeamUser::with(['team','user'])->findOrFail($id);
        $response = [
            'message' => 'View Successfully ',
            'data' => $team,
        ];
        return response()->json($response, 201);
    }

    /**
     * Update the specified team user.
     *
     * @group Team User Management
     *
     * @urlParam team required The ID of the team to update. Example: 1
     * @bodyParam teamId int required.
     * @bodyParam userId int required.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  TeamUser $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function update($id, Request $request,)
    {
        $validatedData = $request->validate([
            'teamId' => 'required|int',
            'userId' => 'required|int',
        ]);
        $team = TeamUser::with(['team','user'])->findOrFail($id);

        $team->teamId = $validatedData['teamId'];
        $team->userId = $validatedData['userId'];
        $team->save();

        $response = [
            'message' => 'Update Successfully ',
            'data' => $team,
        ];
        return response()->json($response, 201);
    }

    /**
     * Remove the specified team from storage.
     *
     * @group Team User Management
     *
     * @urlParam question required The ID of the question to delete. Example: 1
     * @param  TeamUser $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($id)
    {
        $team = TeamUser::findOrFail($id);
        $team->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 204);
    }
}

