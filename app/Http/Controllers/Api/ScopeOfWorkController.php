<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\ProblemsAndGoals;
use App\Models\ScopeOfWork;
use App\Models\ServiceScopes;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


/**
 * @authenticated
 */

 class ScopeOfWorkController extends Controller
{
    private $promptType = PromptType::SCOPE_OF_WORK;

     /**
      * Get Scope of work list
      *
      * @group Scope Of Work
      * @queryParam page integer page number.
      * @queryParam problemGoalId integer page number.
      * @queryParam per_page integer page number.
      * @queryParam isChecked int filter the selected or not or all, Example: 1
      */
     public function index(Request $request)
     {
         $validatedData = $request->validate([
             'problemGoalId' => 'required|int',
         ]);
         $query = ScopeOfWork::latest()->where('problemGoalId',$request->get('problemGoalId'));

         if($request->has('isChecked')){
             $query->where('isChecked',$request->isChecked);
         }
         // Paginate the results if a page number is provided
         if ($request->has('page')) {
             $data = $query->paginate($request->get('per_page')??10);
             return response()->json([
                 'data' => $data->items(),
                 'total' => $data->total(),
                 'current_page' => $data->currentPage(),
                 'per_page' => $data->perPage(),
             ]);
         }

         // Fetch all data if no page number is provided
         $data = $query->get();
         return response()->json([
             'data' => $data,
         ]);
     }

    /**
     * Create a new Scope Of Work
     *
     * @group Scope Of Work
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam title string required
     */

    public function addNew(Request $request){
        $validatedData = $request->validate([
            'problemGoalId' => 'required|int',
            'title' => 'required|string'
        ]);
       try{
           $problemGoalsObj = ProblemsAndGoals::findOrFail($validatedData['problemGoalId']);

           $scopeWork = new ScopeOfWork();
           $scopeWork->problemGoalID = $problemGoalsObj->id;
           $scopeWork->transcriptId = $problemGoalsObj->transcriptId;
           $scopeWork->title = $request->get("title");
           $scopeWork->save();
           return response()->json($scopeWork, 201);

       }catch (\Exception $exception){
           return WebApiResponse::error(500, $errors = [], $exception->getMessage());
       }
    }


    /**
     * Generate Scope Of Work with AI
     *
     * @group Scope Of Work
     *
     * @bodyParam problemGoalID int required Id of the ProblemsAndGoals.
     */

    public function create(Request $request){

        $prompt = PromptService::findPromptByType($this->promptType);
        if($prompt == null){
            $response = [
                'message' => 'Prompt not set for PromptType::MEETING_SUMMARY',
                'data' => []
            ];
            return response()->json($response, 422);
        }
        $validatedData = $request->validate([
            'problemGoalID' => 'required|int'
        ]);
       try{
           set_time_limit(500);


           $batchId = (string) Str::uuid();


           $findExisting = ScopeOfWork::where('problemGoalID',$validatedData['problemGoalID'])->first();

           if($findExisting){
               return WebApiResponse::error(500, $errors = [], 'The scope of work already generated.');
           }


           $problemGoalsObj = ProblemsAndGoals::with(['meetingTranscript','meetingTranscript.serviceInfo'])->findOrFail($validatedData['problemGoalID']);

           $serviceScope = ServiceScopes::where('projectTypeId',$problemGoalsObj->meetingTranscript->serviceInfo->projectTypeId)->get();



            DB::beginTransaction();

            $problemGoalsObj = ProblemsAndGoals::findOrFail($request->problemGoalID);


            $aiScopes   = OpenAIGeneratorService::generateScopeOfWork($problemGoalsObj->problemGoalText, $prompt->prompt);
            Log::debug(['$aiScopes',$aiScopes]);

            if (!is_array($aiScopes) || count($aiScopes) < 1 || !isset($aiScopes[0]->title)) {
               return WebApiResponse::error(500, $errors = [], 'The scopes from AI is not expected output, Try again please');
            }

            if(count($serviceScope)>0){
                $serviceScopeList = $serviceScope->map(function($scope){
                    return [
                        'scopeId' => $scope->id,
                        'title' => strip_tags($scope->name),
                    ];
                })->toJson();
                Log::debug(['$serviceScopeList',$serviceScopeList]);
                $mergedScope = OpenAIGeneratorService::mergeScopeOfWork($serviceScopeList, json_encode($aiScopes));
                Log::debug(['$mergedScope',$mergedScope]);

                if (!is_array($mergedScope) || count($mergedScope) < 1 || !isset($mergedScope[0]->title)) {
                    return WebApiResponse::error(500, $errors = [], 'The merged result from AI is not expected output, Try again please');
                }
                $this->storeScopeOfWork($mergedScope, $batchId, $problemGoalsObj);
            }else{
                $this->storeScopeOfWork($aiScopes, $batchId, $problemGoalsObj);
            }

           DB::commit();
           $scopeOfWorks = ScopeOfWork::where('problemGoalID', $problemGoalsObj->id)->get();
           return response()->json($scopeOfWorks, 201);

       }catch (\Exception $exception){
           DB::rollBack();
           return WebApiResponse::error(500, $errors = [], $exception->getMessage());
       }
    }

    private  function storeScopeOfWork($mergedScope, $batchId, $problemGoalsObj){
        foreach($mergedScope as $scope){
            $scopeWork = new ScopeOfWork();
            $scopeWork->problemGoalID = $problemGoalsObj->id;
            $scopeWork->transcriptId = $problemGoalsObj->transcriptId;
            $scopeWork->serviceScopeId = !empty($scope->scopeId)? $scope->scopeId : null;
            $scopeWork->scopeText = !empty($scope->details)? $scope->details: null;
            $scopeWork->title = $scope->title;
            $scopeWork->batchId = $batchId;
            $scopeWork->save();
        }
    }

    /**
     * Update select Scope Of Work
     *
     * @group Scope Of Work
     *
     * @bodyParam problemGoalId int required Id of the ProblemsAndGoals.
     * @bodyParam scopeOfWorkId string[] required An array of meeting links. Example: [1,2,3]
     *
     */

    public function select(Request $request){
        try{
            $validatedData = $request->validate([
                'problemGoalId' => 'required|int',
                'scopeOfWorkId' => 'required|array',
            ]);
            $problemGoalId = $validatedData['problemGoalId'];
            $scopeOfWorkIds = $validatedData['scopeOfWorkId'];

            DB::beginTransaction();

            ScopeOfWork::where('problemGoalId', $problemGoalId)
                ->whereIn('id', $scopeOfWorkIds)
                ->update(['isChecked' => 1]);

            // Update the records that should not be checked
            ScopeOfWork::where('problemGoalId', $problemGoalId)
                ->whereNotIn('id', $scopeOfWorkIds)
                ->update(['isChecked' => 0]);

            DB::commit();

            $response = [
                'message' => 'Scope of work selected successfully',
            ];
            return response()->json($response, 201);
        }catch (\Exception $exception){
            DB::rollBack();
            return WebApiResponse::error(500, $errors = [], $exception->getMessage());
        }
    }
    /**
     * Update Scope Of Work
     *
     * @group Scope Of Work
     *
     * @urlParam id int required Id of the ScopeOfWork.
     * @bodyParam title string required
     *
     */

    public function update($id, Request $request){
        $validatedData = $request->validate([
            'title' => 'required|string',
        ]);

        $scopeOfWork = ScopeOfWork::findOrFail($id);
        $scopeOfWork->title = $request->title;
        $scopeOfWork->save();

        $response = [
            'message' => 'Scope of work updated successfully',
            'data' => $scopeOfWork,
        ];

        return response()->json($response, 201);
    }
}
