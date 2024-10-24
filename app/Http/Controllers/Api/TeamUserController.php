<?php

namespace App\Http\Controllers\Api;

use App\Libraries\WebApiResponse;
use App\Models\PromptSharedTeam;
use App\Models\Team;
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
     * @urlParam teamId integer required The ID of the Team. Example: 1
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($teamId, Request $request)
    {
        $teamQuery = TeamUser::with(['team','user'])->where('teamId',$teamId);

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
     * @urlParam teamId integer required The ID of the Team. Example: 1
     * @bodyParam userId string int of the User.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store($teamId, Request $request)
    {
        $validatedData = $request->validate([
            'userId' => 'required|int',
        ]);

        $verifyTeam = Team::where('id',$teamId)->first();
        if(!$verifyTeam){
            return WebApiResponse::error(404, $errors = [],"Team not found");
        }

        $findExistingPrompt = TeamUser::where('teamId',$teamId)->where('userId',$validatedData['userId'])->first();
        if($findExistingPrompt){
            return WebApiResponse::error(400, $errors = [], 'The user already exists for this team.');
        }
        $teamUser = new TeamUser();
        $teamUser->teamId = $teamId;
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
     * @urlParam teamId integer required The ID of the Team. Example: 1
     * @urlParam id required. The ID of the team user to display. Example: 1
     *
     * @param  TeamUser $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($teamId, $id){
        $team = TeamUser::with(['team','user'])->where('teamId', $teamId)->where('id', $id)->first();
        if(!$team){
            return WebApiResponse::error(404, $errors = [], "Team use not found");
        }
        $response = [
            'message' => 'View Successfully ',
            'data' => $team,
        ];
        return response()->json($response, 201);
    }

    /**
     * Remove the specified team from storage.
     *
     * @group Team User Management
     *
     * @urlParam teamId required The ID of the team to update. Example: 1
     * @urlParam question required The ID of the question to delete. Example: 1
     *
     * @param Team $teamId
     * @param TeamUser $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($teamId, $id)
    {
        $team = TeamUser::where('teamId',$teamId)->findOrFail($id);
        $team->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 204);
    }
}

