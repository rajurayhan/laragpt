<?php

use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\DeliverablesController;
use App\Http\Controllers\Api\EstimationsTasksController;
use App\Http\Controllers\Api\AssociateController;
use App\Http\Controllers\Api\TeamReviewController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\MeetingSummeryController;
use App\Http\Controllers\Api\ProblemAndGoalController;
use App\Http\Controllers\Api\ProjectOverviewController;
use App\Http\Controllers\Api\ProjectSummeryController;
use App\Http\Controllers\Api\ScopeOfWorkController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\Services\ProjectController;
use App\Http\Controllers\Api\Services\WebsiteComponentCategoryController;
use App\Http\Controllers\Api\Services\WebsiteComponentController;
use App\Http\Controllers\Api\Services\ProjectComponentController;
use App\Http\Controllers\Api\PromptController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\Services\EmployeeRoleController;
use App\Http\Controllers\Api\Services\ServiceController;
use App\Http\Controllers\Api\Services\ServiceDeliverablesController;
use App\Http\Controllers\Api\Services\ServiceDeliverableTasksController;
use App\Http\Controllers\Api\Services\ServiceGroupController;
use App\Http\Controllers\Api\Services\ServiceScopeController;
use App\Http\Controllers\Api\System\MeetingTypeController;
use App\Http\Controllers\Api\System\ProjectTypeController;
use App\Http\Controllers\Api\UpdateLogController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\QuestionController;
use App\Libraries\ContentGenerator;
use App\Libraries\WebApiResponse;
use App\Models\Services;
use App\Services\ClickUpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {

    // Users routes
    Route::get('/users', [UserController::class, 'index'])->name('user.list');
    Route::post('/users', [UserController::class, 'store'])->name('user.create');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('user.show');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('user.delete');

    // Question Setup routes
    Route::get('/questions', [QuestionController::class, 'index'])->name('user.list');
    Route::post('/questions', [QuestionController::class, 'store'])->name('user.create');
    Route::get('/questions/{id}', [QuestionController::class, 'show'])->name('user.show');
    Route::put('/questions/{id}', [QuestionController::class, 'update'])->name('user.update');
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy'])->name('user.delete');

    // Prompts routes
    Route::get('/prompts', [PromptController::class, 'index'])->name('prompt.list');
    Route::post('/prompts', [PromptController::class, 'store'])->name('prompt.create');
    Route::get('/prompts/{id}', [PromptController::class, 'show'])->name('prompt.show');
    Route::put('/prompts/{id}', [PromptController::class, 'update'])->name('prompt.update');
    Route::delete('/prompts/{id}', [PromptController::class, 'destroy'])->name('prompt.delete');


    // project-summery routes
    Route::get('/project-summery', [ProjectSummeryController::class, 'index'])->name('project.summery.list');
    Route::post('/project-summery', [ProjectSummeryController::class, 'store'])->name('project.summery.create');
    Route::get('/project-summery/{id}', [ProjectSummeryController::class, 'show'])->name('project.summery.show');
    Route::put('/project-summery/{id}', [ProjectSummeryController::class, 'update'])->name('project.summery.update');
    Route::delete('/project-summery/{id}', [ProjectSummeryController::class, 'delete'])->name('project.summery.delete');

    Route::get('/meeting-summery', [MeetingSummeryController::class, 'indexMeetingSummery'])->name('meeting.summery.list');
    Route::post('/meeting-summery', [MeetingSummeryController::class, 'storeMeetingSummery'])->name('meeting.summery.create');
    Route::get('/meeting-summery/{id}', [MeetingSummeryController::class, 'showMeetingSummery'])->name('meeting.summery.show');
    Route::put('/meeting-summery/{id}', [MeetingSummeryController::class, 'updateMeetingSummery'])->name('meeting.summery.update');
    Route::delete('/meeting-summery/{id}', [MeetingSummeryController::class, 'deleteMeetingSummery'])->name('meeting.summery.update');

    // problems-goals api
    Route::post('/problems-and-goals', [ProblemAndGoalController::class, 'create'])->name('problems.goals.create');
    Route::post('/problems-and-goals/{id}', [ProblemAndGoalController::class, 'update'])->name('problems.goals.update');

    // project-overview routes
    Route::post('/project-overview', [ProjectOverviewController::class, 'create'])->name('project.overview.create');
    Route::post('/project-overview/{id}', [ProjectOverviewController::class, 'update'])->name('project.overview.update');

    Route::get('/scope-of-work', [ScopeOfWorkController::class, 'index'])->name('scope.of.work.list');
    Route::post('/scope-of-work/add-new', [ScopeOfWorkController::class, 'addNew'])->name('scope.of.work.add.new');
    Route::post('/scope-of-work', [ScopeOfWorkController::class, 'create'])->name('scope.of.work.create');
    Route::post('/scope-of-work/{id}', [ScopeOfWorkController::class, 'update'])->name('scope.of.work.update');
    Route::post('/scope-of-work-select', [ScopeOfWorkController::class, 'select'])->name('scope.of.work.select');

    // deliverables api

    Route::get('/deliverables', [DeliverablesController::class, 'index'])->name('deliverables.list');
    Route::post('/deliverables/add-new', [DeliverablesController::class, 'addNew'])->name('deliverables.add.new');
    Route::post('/deliverables', [DeliverablesController::class, 'create'])->name('deliverables.create');
    Route::post('/deliverables/{id}', [DeliverablesController::class, 'update'])->name('deliverables.update');
    Route::post('/deliverables-select', [DeliverablesController::class, 'select'])->name('deliverables.select');
    Route::post('/deliverables-select/additional-service', [DeliverablesController::class, 'selectAdditionalDeliverable'])->name('deliverables.additional.service.select');

    // estimation tasks api

    Route::get('/estimation-tasks', [EstimationsTasksController::class, 'index'])->name('estimation.tasks.list');
    Route::post('/estimation-tasks/add-new', [EstimationsTasksController::class, 'addNew'])->name('estimation.tasks.add.new');
    Route::post('/estimation-tasks', [EstimationsTasksController::class, 'create'])->name('estimation.tasks.create');
    Route::post('/estimation-tasks/{id}', [EstimationsTasksController::class, 'update'])->name('estimation.tasks.update');
    Route::post('/estimation-tasks/{id}/add-associate', [EstimationsTasksController::class, 'addAssociate'])->name('estimation.tasks.add.associate');

    //Associates
    Route::get('/associates', [AssociateController::class, 'index'])->name('associates.list');

    //Team Review
    Route::post('/team-review', [TeamReviewController::class, 'saveTeamReview'])->name('team.review.save');

    Route::apiResource('projects', ProjectController::class);

    Route::apiResource('categories', WebsiteComponentCategoryController::class);
    Route::apiResource('components', WebsiteComponentController::class);
    Route::apiResource('project-components', ProjectComponentController::class);

    Route::apiResource('meeting-type', MeetingTypeController::class);
    Route::apiResource('project-type', ProjectTypeController::class);

    Route::apiResource('leads', LeadController::class);

    Route::apiResource('employee-roles', EmployeeRoleController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('service-groups', ServiceGroupController::class);
    Route::apiResource('service-scopes', ServiceScopeController::class);
    Route::apiResource('service-deliverables', ServiceDeliverablesController::class);
    Route::apiResource('service-deliverable-tasks', ServiceDeliverableTasksController::class);

    Route::apiResource('roles', RoleController::class);

    Route::get('/service-tree', [ServiceController::class, 'serviceTree'])->name('service.tree');

    Route::resource('update-logs', UpdateLogController::class);

    Route::get('/permissions', function () {
        $permissions =  Permission::get('name');

        $formattedPermissions = [];

        foreach ($permissions as $permission) {
            $nameParts = explode('.', $permission['name']);
            $moduleName = $nameParts[1];

            if(isset($formattedPermissions[$moduleName])){
                array_push($formattedPermissions[$moduleName], $permission['name']);
            }
            else{
                $formattedPermissions[$moduleName][] = $permission['name'];
            }

        }

        $response = [
                'message' => 'Data Fetched Successfully',
                'data' => $formattedPermissions
            ];

            return response()->json($response, 201);
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::group(['prefix' => 'conversations'], function () {
        Route::get('/', [ConversationController::class, 'index']);
        Route::get('/{id}', [ConversationController::class, 'show']);
        Route::put('/{id}', [ConversationController::class, 'update']);
        Route::delete('/{id}', [ConversationController::class, 'delete']);
        Route::post('/create', [ConversationController::class, 'createConversation']);
        Route::post('/continue', [ConversationController::class, 'continueConversation']);

        Route::put('/message/{id}', [ConversationController::class, 'updateConversationMessage']);
    });


    // Route::get('/clickup/list', [ServiceDeliverableTasksController::class, 'fetchTasksFromListId'])->name('task.from.clickup');

});

Route::get('/task-tree', function () {
    $servicesRawData =  Services::with([
        'serviceGroups.serviceScopes.serviceDeliverables.serviceDeliverableTasks' => function ($query) {
            $query->where('parentTaskId', null)->with('subTasks');
        },
    ])->get();

    $services = [];

    foreach ($servicesRawData as $service) {
        $serviceData = [
            'id' => $service->id,
            'name' => $service->name,
            'groups' => [],
        ];

        foreach ($service->serviceGroups as $group) {
            $serviceGroupData = [
                'id' => $group->id,
                'name' => $group->name,
                'sows' => [],
            ];

            foreach ($group->serviceScopes as $scope) {
                $scopedata = [
                    'id' => $scope->id,
                    'name' => $scope->name,
                    'deliverables' => [],
                ];

                foreach ($scope->serviceDeliverables as $deliverable) {
                    $deliverableData = [
                        'id' => $deliverable->id,
                        'name' => $deliverable->name,
                        'tasks' => [],
                    ];

                    foreach ($deliverable->serviceDeliverableTasks as $task) {
                        $taskData = [
                            'id' => $task->id,
                            'name' => $task->name,
                            'description' => $task->description,
                            'sub_tasks' => [],
                        ];

                        foreach ($task->subTasks as $subTask) {
                            $subTaskData = [
                                'id' => $subTask->id,
                                'name' => $subTask->name,
                                'description' => $subTask->description,
                            ];

                            $taskData['sub_tasks'][] = $subTaskData;
                        }

                        $deliverableData['tasks'][] = $taskData;
                    }

                    $scopedata['deliverables'][] = $deliverableData;
                }

                $serviceGroupData['sows'][] = $scopedata;
            }

            $serviceData['groups'][] = $serviceGroupData;
        }

        $services[] = $serviceData;
    }

    return response()->json(['services' => $services]);

});

Route::post('/clickup/list', [ServiceDeliverableTasksController::class, 'fetchTasksFromListId'])->name('task.from.clickup');




Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

// Others
Route::post('/completion', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'prompt' => 'required'
    ]);

    if($validator->fails()){
        return WebApiResponse::validationError($validator, $request);
    }
    $prompt = "Write a complete article on this topic:\n\n" . $request->prompt ."\n\n in 200 words";
    $returnResponse =  ContentGenerator::completion($prompt);
    return WebApiResponse::success(200, $returnResponse['choices'][0], 'Generated Successfully');
})->name('completion');

Route::post('/image', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'prompt' => 'required'
    ]);

    if($validator->fails()){
        return WebApiResponse::validationError($validator, $request);
    }

    return response()->json(ContentGenerator::image($request));
})->name('image');
