<?php

use App\Http\Controllers\Api\ProblemAndGoalController;
use App\Http\Controllers\Api\ProjectOverviewController;
use App\Http\Controllers\Api\ProjectSummeryController;
use App\Http\Controllers\Api\ScopeOfWorkController;
use App\Libraries\ContentGenerator;
use App\Libraries\WebApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

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

// project-summery routes
Route::get('/project-summery', [ProjectSummeryController::class, 'index'])->name('project.summery.list');
Route::post('/project-summery', [ProjectSummeryController::class, 'store'])->name('project.summery.create');
Route::get('/project-summery/{id}', [ProjectSummeryController::class, 'show'])->name('project.summery.show');
Route::put('/project-summery/{id}', [ProjectSummeryController::class, 'update'])->name('project.summery.update');
Route::delete('/project-summery/{id}', [ProjectSummeryController::class, 'delete'])->name('project.summery.delete');

// problems-goals api
Route::post('/problems-and-goals', [ProblemAndGoalController::class, 'create'])->name('problems.goals.create');
Route::post('/problems-and-goals/{id}', [ProblemAndGoalController::class, 'update'])->name('problems.goals.update');

// project-overview routes
Route::post('/project-overview', [ProjectOverviewController::class, 'create'])->name('project.overview.create');
Route::post('/project-overview/{id}', [ProjectOverviewController::class, 'update'])->name('project.overview.update');

Route::post('/scope-of-work', [ScopeOfWorkController::class, 'create'])->name('scope.of.work.create');
Route::post('/scope-of-work/{id}', [ScopeOfWorkController::class, 'update'])->name('scope.of.work.update');

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
