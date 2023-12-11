<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deliberable;
use App\Models\ScopeOfWork;
use App\Services\OpenAIGeneratorService;
use Illuminate\Http\Request;

class DeliverablesController extends Controller
{
    /**
     * Create Deliberable
     *
     * @group Deliberable
     *
     * @bodyParam scopeOfWorkId int required Id of the ScopeOfWork.
     */

    public function create(Request $request){
        $validatedData = $request->validate([
            'scopeOfWorkId' => 'required|int'
        ]);

        $scopeOfWorkObj      = ScopeOfWork::findOrFail($request->scopeOfWorkId);
        $deliverables   = OpenAIGeneratorService::generateDeliverables($scopeOfWorkObj->scopeText);

        $deliverablesObj = Deliberable::updateOrCreate(
            ['scopeOfWorkId' => $request->transcriptId],
            ['deliverablesText' => $deliverables]
        );

        $response = [
            'message' => 'Created Successfully ',
            'data' => $deliverablesObj,
        ];

        return response()->json($response, 201);
    }

    /**
     * Update Deliberable
     *
     * @group Deliberable
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
