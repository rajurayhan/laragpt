<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\Workflow;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    /**
     * @group Workflow
     * Display a listing of the workflows.
     * @queryParam page integer page number.
     * @queryParam per_page integer Number of items per page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $workflowsQuery = Workflow::query();
        $workflows = $workflowsQuery->orderBy('title','ASC')->paginate($request->get('per_page')??10);
        return response()->json($workflows);
    }


    /**
     * @group Workflow
     * Store a newly created workflow in storage.
     *
     * @bodyParam title string required The name of the workflow. Example: "Example name of a Workflow "
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $workflow = Workflow::create($validated);

        return response()->json($workflow, 201);
    }

    /**
     * @group Workflow
     * Display the specified workflow.
     *
     * @param  \App\Models\Workflow  $workflow
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $workflow = Workflow::with(['steps'])->where('id',$id)->first();

            if(!$workflow){
                return WebApiResponse::error(404, $errors = [], 'Workflow not found');
            }
            $response = [
                'message' => 'Data Showed Successfully',
                'data' => $workflow
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching projectType details', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @group Workflow
     * Update the specified workflow in storage.
     *
     * @bodyParam title string required The name of the workflow. Example: "Example name of a Workflow "
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Workflow  $workflow
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Workflow $workflow)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $workflow->update($validated);

        return response()->json($workflow, 200);
    }

    /**
     * @group Workflow
     * Remove the specified workflow from storage.
     *
     * @param  \App\Models\Workflow  $workflow
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $workflow = Workflow::where('id',$id)->first();

        if(!$workflow){
            return WebApiResponse::error(404, $errors = [], 'Workflow not found');
        }
        $workflow->delete();
        return response()->json(null, 204);
    }

    /**
     * @group Workflow
     * Activate the specified workflow.
     *
     * Activates a workflow, setting its status to active (1).
     *
     * @param  \App\Models\Workflow  $workflow
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate($id)
    {
        $workflow = Workflow::where('id',$id)->first();

        if(!$workflow){
            return WebApiResponse::error(404, $errors = [], 'Workflow not found');
        }

        $workflow->status = 1; // Set status to 1 (active)
        $workflow->save();
        return response()->json($workflow, 200);
    }

    /**
     * @group Workflow
     * Deactivate the specified workflow.
     *
     * Deactivates a workflow, setting its status to inactive (0).
     *
     * @param  \App\Models\Workflow  $workflow
     * @return \Illuminate\Http\JsonResponse
     */
    public function deactivate($id)
    {
        $workflow = Workflow::where('id',$id)->first();


        if(!$workflow){
            return WebApiResponse::error(404, $errors = [], 'Workflow not found');
        }
        $workflow->status = 0; // Set status to 0 (inactive)
        $workflow->save();
        return response()->json($workflow, 200);
    }
}
