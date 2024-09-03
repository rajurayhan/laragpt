<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\MeetingTranscript;
use App\Models\ProblemsAndGoals;
use App\Models\ProjectOverview;
use App\Models\Prompt;
use App\Services\Markdown2Html;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @authenticated
 */

 class ProjectOverviewController extends Controller
{
    private $promptType = PromptType::PROJECT_OVERVIEW;

    /**
     * Create Project Overview
     *
     * @group Project Overview
     *
     * @bodyParam problemGoalID int required Id of the ProblemsAndGoals.
     */

    public function create(Request $request){
        set_time_limit(500);
        $validatedData = $request->validate([
            'problemGoalID' => 'required|int'
        ]);

        $prompts = Prompt::where('type',$this->promptType)->orderBy('serial','ASC')->get();
        if(count($prompts) < 1){
            $response = [
                'message' => 'Prompt not set for PromptType::PROJECT_OVERVIEW',
                'data' => []
            ];
            return response()->json($response, 422);
        }


        $problemGoalsObj = ProblemsAndGoals::findOrFail($validatedData['problemGoalID']);
        $transcriptObj = MeetingTranscript::findOrFail($problemGoalsObj->transcriptId);
        $input = [
            "{CLIENT-EMAIL}" => $transcriptObj->clientEmail,
            "{CLIENT-COMPANY-NAME}" => $transcriptObj->company,
            "CLIENT-COMPANY-NAME" => $transcriptObj->company,
            "{CLIENT-PHONE}" => $transcriptObj->clientPhone,
        ];

        $response = Http::timeout(450)->post(env('AI_APPLICATION_URL').'/estimation/project-overview-generate', [
            'threadId' => $transcriptObj->threadId,
            'assistantId' => $transcriptObj->assistantId,
            'prompts' => $prompts->map(function ($item, $key) {
                return [
                    'prompt_text'=> $item->prompt,
                    'action_type'=> $item->action_type,
                ];
            })->toArray(),
        ]);

        if (!$response->successful()) {
            WebApiResponse::error(500, $errors = [], "Can't able to problem and goals, Please try again.");
        }
        Log::info(['Problem And Goal Generate AI.',$response]);
        $data = $response->json();

        $projectOverview = strip_tags($data['data']['projectOverview']);
        foreach ($input as $key => $value) {
            $placeholder = $key;
            $projectOverview = str_replace($placeholder, $value, $projectOverview);
        }

        ProjectOverview::updateOrCreate(
            ['problemGoalID' => $request->problemGoalID],
            ['overviewText' => Markdown2Html::convert($projectOverview)]
        );

        $projectOverviewNew = ProjectOverview::where('problemGoalID', $problemGoalsObj->id)->first();

        $response = [
            'message' => 'Created Successfully ',
            'data' => $projectOverviewNew,
        ];

        return response()->json($response, 201);
    }

    /**
     * Update Project Overview
     *
     * @group Project Overview
     *
     * @urlParam id int required Id of the ProjectOverview.
     * @bodyParam overviewText string required text of the ProjectOverview.
     */

    public function update($id, Request $request){
        $validatedData = $request->validate([
            'overviewText' => 'required|string'
        ]);

        $projectOverview = ProjectOverview::findOrFail($id);
        $projectOverview->overviewText = $request->overviewText;

        $projectOverview->save();

        $response = [
            'message' => 'Created Successfully ',
            'data' => $projectOverview,
        ];

        return response()->json($response, 201);
    }
}
