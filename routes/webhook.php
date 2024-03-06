<?php

use App\Http\Controllers\Webhook\LeadsWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Route::get('/leads-webhook', function (Request $request) {
    \Log::info(["LHG Leads" => $request->all()]);
    return response()->json(["SUCCESS"]);
})->name('lhg.leads.webhook');

Route::get('/leads-webhook', [LeadsWebhookController::class, 'handleLHGLeadWebookData'])->name('lhg.leads.webhook');
// Route::get('/leads-webhook', function (Request $request) {
//     \Log::info(["LHG Leads" => $request->all()]);
//     return response()->json(["SUCCESS"]);
// })->name('lhg.leads.webhook');
