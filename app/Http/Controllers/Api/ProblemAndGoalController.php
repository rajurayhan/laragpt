<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Models\MeetingTranscript;
use App\Models\ProblemsAndGoals;
use App\Services\OpenAIGeneratorService;
use Illuminate\Http\Request;

class ProblemAndGoalController extends Controller
{

    private $promptType = PromptType::PROBLEMS_AND_GOALS;

    /**
     * Create Problems and Goals
     *
     * @group Problems and Goals
     *
     * @bodyParam transcriptId int required Id of the transcript.
     */

    public function create(Request $request){
        $validatedData = $request->validate([
            'transcriptId' => 'required|int'
        ]);

        $transcriptObj      = MeetingTranscript::findOrFail($request->transcriptId);
        $problemsAndGoals   = OpenAIGeneratorService::generateProblemsAndGoals($transcriptObj->transcriptText);

        $problemsAndGoalsObj = ProblemsAndGoals::updateOrCreate(
            ['transcriptId' => $request->transcriptId],
            ['problemGoalText' => $problemsAndGoals]
        );

        $response = [
            'message' => 'Created Successfully ',
            'data' => $problemsAndGoalsObj,
        ];

        return response()->json($response, 201);
    }

    /**
     * Update Problems and Goals
     *
     * @group Problems and Goals
     *
     * @urlParam id int required Id of the problems and goals.
     * @bodyParam problemGoalText string required text of the ProblemsAndGoals.
     */

    public function update($id, Request $request){
        $validatedData = $request->validate([
            'problemGoalText' => 'required|string'
        ]);

        $problemsAndGoals = ProblemsAndGoals::findOrFail($id);
        $problemsAndGoals->problemGoalText = $request->problemGoalText;

        $problemsAndGoals->save();

        $response = [
            'message' => 'Created Successfully ',
            'data' => $problemsAndGoals,
        ];

        return response()->json($response, 201);
    }
}
