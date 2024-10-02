<?php

namespace App\Http\Controllers\Api;


use App\Libraries\WebApiResponse;
use App\Models\PromptSharedTeam;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * @group Team Management
 * @authenticated
 *
 * APIs for managing team
 */
class TeamController extends Controller
{

    /**
     * Display a listing of Team.
     *
     * @group Team Management
     * @queryParam page integer page number.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $teamQuery = Team::query();

        if ($request->has('page')) {
            $teamPagination = $teamQuery->paginate(10);
            $teamData = $teamPagination->items();
            return response()->json([
                'data' => $this->getPromptAndTeamCount($teamData),
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

    private function getPromptAndTeamCount(&$teams){
        $teamId = collect(array_map(function ($team){
            return $team->id;
        },$teams))->join(', ');

        $promptCount = collect(DB::select('SELECT COUNT(*) as total, teamId FROM prompt_shared_team WHERE teamId IN (' . $teamId .') GROUP BY teamId'))->keyBy('teamId');
        $teamUserCount = collect(DB::select('SELECT COUNT(*) as total, teamId FROM team_users WHERE deleted_at IS NULL AND teamId IN ('. $teamId .') GROUP BY teamId'))->keyBy('teamId');

        array_map(function ($team) use ($promptCount, $teamUserCount){
            $team->promptCount = isset($promptCount[$team->id])? $promptCount[$team->id]->total: 0;
            $team->userCount = isset($teamUserCount[$team->id])? $teamUserCount[$team->id]->total: 0;
            return $team;
        },$teams);

        return $teams;
    }

    /**
     * Store a new Team
     *
     * @group Team Management
     *
     * @bodyParam name string required.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);

        $team = new Team();
        $team->name = $validatedData['name'];
        $team->save();


        $response = [
            'message' => 'Created Successfully ',
            'data' => $team,
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified team.
     *
     * @group Team Management
     *
     * @urlParam team required The ID of the team to display. Example: 1
     *
     * @param  Team $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($id)
    {
        $team = Team::findOrFail($id);
        $response = [
            'message' => 'View Successfully ',
            'data' => $team,
        ];
        return response()->json($response, 201);
    }

    /**
     * Update the specified team.
     *
     * @group Team Management
     *
     * @urlParam team required The ID of the team to update. Example: 1
     * @bodyParam name string required.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Team $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function update($id, Request $request,)
    {
        $validatedData = $request->validate([
            'name' => 'string|max:255',
        ]);
        $team = Team::findOrFail($id);

        $team->name = $validatedData['name'];
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
     * @group Team Management
     *
     * @urlParam question required The ID of the question to delete. Example: 1
     * @param  Team $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($id)
    {
        $team = Team::findOrFail($id);
        $team->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 204);
    }

    /**
     * Get prompt share list
     *
     * @group Team Management
     * @urlParam id integer required The ID of the Team. Example: 1
     */

    public function shareList($id, Request $request){
        try{
            $promptTeams = PromptSharedTeam::with(['prompt','team'])->where('teamId',$id);


            if ($request->has('page')) {
                $teamPagination = $promptTeams->paginate(10);
                $data = array_map(function($item){
                    unset($item->prompt['prompt']);
                    return $item;
                }, $teamPagination->items() );
                return response()->json([
                    'data' => $data,
                    'total' => $teamPagination->total(),
                    'current_page' => $teamPagination->currentPage(),
                ]);
            } else {
                $team = $promptTeams->get();
                $data = $team->map(function($item){
                    unset($item->prompt['prompt']);
                    return $item;
                });
                return response()->json([
                    'data' => $data,
                    'total' => $team->count(),
                ]);
            }


        }catch (\Exception $exception){
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }

    }

    /**
     * Share a Prompt to team
     * @group Team Management
     * @urlParam id integer required The ID of the Team. Example: 1
     * @bodyParam promptId int required. Example: 2
     */

    public function share($id, Request $request){
        try{
            $validatedData = $request->validate([
                'promptId' => 'required|int',
            ]);

            $verifyTeam = Team::where('id',$id)->first();
            if(!$verifyTeam){
                return WebApiResponse::error(404, $errors = [],"Team not found");
            }

            $findExistingPrompt = PromptSharedTeam::where('teamId',$id)->where('promptId',$validatedData['promptId'])->first();
            if($findExistingPrompt){
                return WebApiResponse::error(400, $errors = [], 'The prompt already exists for this team.');
            }
            $promptTeam = new PromptSharedTeam();
            $promptTeam->promptId = $validatedData['promptId'];
            $promptTeam->teamId = $id;
            $promptTeam->save();
            $promptTeam->load(['prompt','team']);
            unset($promptTeam->prompt->prompt);

            $response = [
                'message' => 'Shared Successfully ',
                'data' => $promptTeam,
            ];
            return response()->json($response, 200);

        }catch (\Exception $exception){
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }

    }

    /**
     * Remove Share a Conversation
     * @group Team  Management
     *
     * @urlParam id integer required The ID of the Team. Example: 1
     * @urlParam sharePromptId integer required The ID of the PromptSharedTeam. Example: 1
     */

    public function removeShare($id, $sharePromptId){


        $sharePromptFind = PromptSharedTeam::where('teamId',$id)->where('id',$sharePromptId)->first();

        if(!$sharePromptFind){
            return WebApiResponse::error(404, $errors = [],"Shared prompt not found");
        }

        PromptSharedTeam::where('teamId',$id)->where('id',$sharePromptId)->delete();

        $response = [
            'message' => 'Shared Remove Successfully ',
            'data' => [],
        ];
        return response()->json($response, 200);

    }
}

