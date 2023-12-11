<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Models\ProblemsAndGoals;
use App\Models\ScopeOfWork;
use App\Services\OpenAIGeneratorService;
use Illuminate\Http\Request;

class ScopeOfWorkController extends Controller
{
    private $promptType = PromptType::SCOPE_OF_WORK;

    /**
     * Create Scope Of Work
     *
     * @group Scope Of Work
     *
     * @bodyParam problemGoalID int required Id of the ProblemsAndGoals.
     */

    public function create(Request $request){
        set_time_limit(500);
        $validatedData = $request->validate([
            'problemGoalID' => 'required|int'
        ]);

        $problemGoalsObj      = ProblemsAndGoals::findOrFail($request->problemGoalID);
        $scopeOfWork   = OpenAIGeneratorService::generateScopeOfWork($problemGoalsObj->problemGoalText);

        $scopeOfWorkObj = ScopeOfWork::updateOrCreate(
            ['problemGoalID' => $request->problemGoalID],
            ['scopeText' => $scopeOfWork]
        );

        $response = [
            'message' => 'Created Successfully ',
            'data' => $scopeOfWorkObj,
        ];

        return response()->json($response, 201);
    }

    /**
     * Update Scope Of Work
     *
     * @group Scope Of Work
     *
     * @urlParam id int required Id of the Scope of Work.
     * @bodyParam scopeText string required text of the Scope of Work.
     */

    public function update($id, Request $request){
        $validatedData = $request->validate([
            'scopeText' => 'required|string'
        ]);

        $scopeOfWork = ScopeOfWork::findOrFail($id);
        $scopeOfWork->scopeText = $request->scopeText;

        $scopeOfWork->save();

        $response = [
            'message' => 'Created Successfully ',
            'data' => $scopeOfWork,
        ];

        return response()->json($response, 201);
    }
}
