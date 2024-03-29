<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Models\Deliberable;
use App\Models\ScopeOfWork;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use Illuminate\Http\Request;

/** 
 * @authenticated 
 */

class DeliverablesController extends Controller
{
    
    private $promptType = PromptType::DELIVERABLES;
    /**
     * Create Deliverable
     *
     * @group Deliverable
     *
     * @bodyParam scopeOfWorkId int required Id of the ScopeOfWork.
     */

    public function create(Request $request){
        set_time_limit(500);
        $prompt = PromptService::findPromptByType($this->promptType);
        if($prompt == null){
            $response = [
                'message' => 'Prompt not set for PromptType::DELIVERABLES',
                'data' => []
            ];
            return response()->json($response, 422);
        }
        $validatedData = $request->validate([
            'scopeOfWorkId' => 'required|int'
        ]);

        $scopeOfWorkObj      = ScopeOfWork::findOrFail($request->scopeOfWorkId);
        $deliverables   = OpenAIGeneratorService::generateDeliverables($scopeOfWorkObj->scopeText, $prompt->prompt);

        $deliverablesObj = Deliberable::updateOrCreate(
            ['scopeOfWorkId' => $request->scopeOfWorkId],
            ['deliverablesText' => $deliverables]
        );

        $response = [
            'message' => 'Created Successfully ',
            'data' => $deliverablesObj,
        ];

        return response()->json($response, 201);
    }

    /**
     * Update Deliverable
     *
     * @group Deliverable
     *
     * @urlParam id int required Id of the Deliverables.
     * @bodyParam deliverablesText string required text of the Deliverables.
     */

    public function update($id, Request $request){
        $validatedData = $request->validate([
            'deliverablesText' => 'required|string'
        ]);

        $deliverables = Deliberable::findOrFail($id);
        $deliverables->deliverablesText = $request->deliverablesText;

        $deliverables->save();

        $response = [
            'message' => 'Created Successfully ',
            'data' => $deliverables,
        ];

        return response()->json($response, 201);
    }
}
