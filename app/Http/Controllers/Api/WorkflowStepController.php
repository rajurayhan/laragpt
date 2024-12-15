<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowStepController extends Controller
{
    /**
     * @group Workflow Steps
     * Display a listing of the workflow steps.
     *
     * @queryParam workflow_id Id of workflow.
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $workflowSteps = WorkflowStep::with(['prompt'])->where('workflow_id',$request->workflow_id)->orderBy('serial','ASC')->get();
        return WebApiResponse()->json($workflowSteps);
    }

    /**
     * @group Workflow Steps
     * Store a newly created workflow step in storage at a specific position.
     *
     * Inserts the step at the desired serial position, adjusting subsequent serials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'prompt_id' => 'required|exists:prompts,id',
            'serial' => 'required|integer|min:1',
        ]);
        $findExisting = WorkflowStep::where('workflow_id', $validated['workflow_id'])->where('prompt_id', $validated['prompt_id'])->first();
        if($findExisting){
            return WebApiResponse::error(404, $errors = [], 'The prompt already exists in another step');
        }
        DB::beginTransaction();
        try {
            // Adjust the serial numbers of existing steps
            WorkflowStep::where('workflow_id', $validated['workflow_id'])->where('serial', '>=', $validated['serial'])
                ->increment('serial');

            // Insert the new workflow step
            $step = WorkflowStep::create($validated);

            DB::commit();

            return response()->json($step, 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to create workflow step'], 500);
        }
    }

    /**
     * @group Workflow Steps
     * Display the specified workflow step.
     *
     * @param  \App\Models\WorkflowStep  $workflowStep
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(WorkflowStep $workflowStep)
    {
        try {
            return response()->json($workflowStep);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch workflow step'], 500);
        }
    }

    /**
     * @group Workflow Steps
     * Update the specified workflow step in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WorkflowStep  $workflowStep
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WorkflowStep $workflowStep)
    {
        $validated = $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'prompt_id' => 'required|exists:prompts,id',
            'serial' => 'required|integer|min:1',
        ]);

        // Check if the new prompt_id already exists within the specified workflow_id
        $findExisting = WorkflowStep::where('workflow_id', $validated['workflow_id'])
            ->where('prompt_id', $validated['prompt_id'])
            ->where('id', '!=', $workflowStep->id) // Exclude the current step from the check
            ->first();

        if ($findExisting) {
            return response()->json(['error' => 'The prompt already exists in another step'], 400);
        }


        DB::beginTransaction();
        try {
            if ($workflowStep->serial != $validated['serial']) {
                if ($workflowStep->serial < $validated['serial']) {
                    // If the current serial is less than the new one, decrement all steps in between
                    WorkflowStep::where('workflow_id', $validated['workflow_id'])->where('serial', '>', $workflowStep->serial)
                        ->where('serial', '<=', $validated['serial'])
                        ->decrement('serial');
                } else {
                    // If the current serial is greater than the new one, increment all steps in between
                    WorkflowStep::where('workflow_id', $validated['workflow_id'])->where('serial', '<', $workflowStep->serial)
                        ->where('serial', '>=', $validated['serial'])
                        ->increment('serial');
                }
            }

            // Update workflow step attributes
            $workflowStep->update($validated);

            DB::commit();

            return response()->json($workflowStep, 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to update workflow step'], 500);
        }
    }

    /**
     * @group Workflow Steps
     * Remove the specified workflow step from storage.
     *
     * @param  \App\Models\WorkflowStep  $workflowStep
     */
    public function destroy(WorkflowStep $workflowStep)
    {
        DB::beginTransaction();
        try {
            $deletedSerial = $workflowStep->serial;
            $workflowStep->delete();
            // Adjust the serial numbers of the remaining steps
            WorkflowStep::where('serial', '>', $deletedSerial)
                ->decrement('serial');
            DB::commit();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to delete workflow step'], 500);
        }
    }
}
