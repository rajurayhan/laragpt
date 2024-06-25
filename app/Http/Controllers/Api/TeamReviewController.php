<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\Deliberable;
use App\Models\DeliverablesNotes;
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
     * @bodyParam transcriptId int required Id of the ProblemsAndGoals.
     * @bodyParam teams object[] required An array of notes details.
     * @bodyParam teams[].employeeRoleId int required. Example: 1
     * @bodyParam teams[].associateId int required. Example: 2
     *
     */

    public function saveTeamReview(Request $request){
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
                $deliverablesNotes = new ProjectTeam();
                $deliverablesNotes->transcriptId = $transcript->id;
                $deliverablesNotes->employeeRoleId = $team['employeeRoleId'];
                $deliverablesNotes->associateId = $team['associateId'];
                $deliverablesNotes->save();
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
}
