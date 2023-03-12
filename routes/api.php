<?php

use App\Libraries\ContentGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/completion', function (Request $request) {
    return response()->json(ContentGenerator::completion($request));
})->name('completion');

Route::post('/image', function (Request $request) {
    return response()->json(ContentGenerator::image($request));
})->name('image');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
