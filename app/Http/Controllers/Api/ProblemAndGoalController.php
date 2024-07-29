<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\MeetingLink;
use App\Models\MeetingTranscript;
use App\Models\ProblemsAndGoals;
use App\Models\Prompt;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @authenticated
 */

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
        set_time_limit(500);
        $prompts = Prompt::where('type',$this->promptType)->orderBy('id','ASC')->get();
        if(count($prompts) < 1){
            $response = [
                'message' => 'Prompt not set for PromptType::PROBLEMS_AND_GOALS',
                'data' => []
            ];
            return response()->json($response, 422);
        }

        $validatedData = $request->validate([
            'transcriptId' => 'required|int'
        ]);

        $transcriptObj = MeetingTranscript::findOrFail($validatedData['transcriptId']);

        $input = [
            "{CLIENT-EMAIL}" => $transcriptObj->clientEmail,
            "{CLIENT-COMPANY-NAME}" => $transcriptObj->company,
            "CLIENT-COMPANY-NAME" => $transcriptObj->company,
            "{CLIENT-PHONE}" => $transcriptObj->clientPhone,
        ];

        $response = Http::timeout(450)->post(env('AI_APPLICATION_URL').'/estimation/problem-and-goal-generate', [
            'threadId' => $transcriptObj->threadId,
            'assistantId' => $transcriptObj->assistantId,
            'prompts' => $prompts->pluck('prompt'),
        ]);

        if (!$response->successful()) {
            WebApiResponse::error(500, $errors = [], "Can't able to problem and goals, Please try again.");
        }
        Log::info(['Problem And Goal Generate AI.',$response]);
        $data = $response->json();

        $problemAndGoalsText = strip_tags($data['data']['problemAndGoals']);
        foreach ($input as $key => $value) {
            $placeholder = $key;
            $problemAndGoalsText = str_replace($placeholder, $value, $problemAndGoalsText);
        }


        $problemsAndGoalsObj = ProblemsAndGoals::updateOrCreate(
            ['transcriptId' => $request->transcriptId],
            ['problemGoalText' => $problemAndGoalsText]
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
