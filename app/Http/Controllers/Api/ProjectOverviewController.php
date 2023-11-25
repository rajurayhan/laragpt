<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProblemsAndGoals;
use App\Models\ProjectOverview;
use App\Services\OpenAIGeneratorService;
use Illuminate\Http\Request;

class ProjectOverviewController extends Controller
{
    /**
     * Create Project Overview
     *
     * @group Project Overview
     *
     * @bodyParam problemGoalID int required Id of the ProblemsAndGoals.
     */

    public function create(Request $request){
        $validatedData = $request->validate([
            'problemGoalID' => 'required|int'
        ]);

        $problemGoalsObj      = ProblemsAndGoals::findOrFail($request->problemGoalID);
        $projectOverview   = OpenAIGeneratorService::generateProjectOverview($problemGoalsObj->problemGoalText);

        $projectOverviewObj = ProjectOverview::updateOrCreate(
            ['problemGoalID' => $request->problemGoalID],
            ['overviewText' => $projectOverview]
        );

        $response = [
            'message' => 'Created Successfully ',
            'data' => $projectOverviewObj,
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
