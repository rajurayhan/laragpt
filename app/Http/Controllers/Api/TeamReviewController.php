<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\Deliberable;
use App\Models\DeliverablesNotes;
use App\Models\EstimationTask;
use App\Models\MeetingTranscript;
use App\Models\ProblemsAndGoals;
use App\Models\ProjectTeam;
use App\Models\ScopeOfWork;
use App\Models\ServiceDeliverables;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @authenticated
 */

class TeamReviewController extends Controller{
    /**
     * Team Review save
     *
     * @group Team Review
     *
     * @bodyParam transcriptId int required id of the transcript.
     * @bodyParam teams object[] required An array of notes details.
     * @bodyParam teams[].employeeRoleId int required. Example: 1
     * @bodyParam teams[].associateId int required. Example: 2
     *
     */

    public function storeTeamReview(Request $request){
        try{
            $validatedData = $request->validate([
                'transcriptId' => 'required|int',
                'teams' => 'required|array',
                'teams.*.employeeRoleId' => 'required|int',
                'teams.*.associateId' => 'required|int',
            ]);
            $teams = $validatedData['teams'];
            ProjectTeam::where('transcriptId',$validatedData['transcriptId'])->delete();
            $transcript = MeetingTranscript::findOrFail($validatedData['transcriptId']);

            DB::beginTransaction();
            foreach ($teams as $team){
                EstimationTask
                    ::where('transcriptId',$validatedData['transcriptId'])
                    ->where('isManualAssociated',false)
                    ->update([
                        "associateId"=>$team['associateId']
                    ]);

                $projectTeam = new ProjectTeam();
                $projectTeam->transcriptId = $transcript->id;
                $projectTeam->employeeRoleId = $team['employeeRoleId'];
                $projectTeam->associateId = $team['associateId'];
                $projectTeam->save();
            }


            DB::commit();

            $response = [
                'message' => 'Team review successfully stored',
            ];
            return response()->json($response, 201);
        }catch (\Exception $exception){
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }
    /**
     * Team Review save
     *
     * @group Team Review
     *
     * @bodyParam transcriptId int required id of the transcript.
     * @bodyParam employeeRoleId int required. Example: 1
     * @bodyParam associateId int required. Example: 2
     *
     */

    public function updateTeamReview(Request $request){
        try{
            $validatedData = $request->validate([
                'transcriptId' => 'required|int',
                'employeeRoleId' => 'required|int',
                'associateId' => 'required|int',
            ]);
            $employeeRoleId = $validatedData['employeeRoleId'];
            $associateId = $validatedData['associateId'];
            ProjectTeam::where('transcriptId',$validatedData['transcriptId'])->delete();
            $transcript = MeetingTranscript::findOrFail($validatedData['transcriptId']);

            DB::beginTransaction();

            EstimationTask
                ::where('transcriptId',$transcript->id)
                ->where('isManualAssociated',false)
                ->update([
                    "associateId"=>$associateId
                ]);

            $projectTeamData = ProjectTeam::updateOrCreate(
                [
                    'transcriptId' => $transcript->id,
                    'employeeRoleId' => $employeeRoleId,
                ],
                ['associateId' => $associateId]
            );


            DB::commit();

            $response = [
                'message' => 'Team review successfully update',
                'data'=>$projectTeamData
            ];
            return response()->json($response, 201);
        }catch (\Exception $exception){
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }
}
