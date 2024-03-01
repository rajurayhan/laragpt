<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SoWGeneratorController;
use App\Models\Services;
use App\Services\ModelOrderManagerService;
use Illuminate\Support\Facades\Http;
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

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('index');
    })->name('dashboard');
});

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/clickup', function () {
    // $baseUrl = "https://api.clickup.com/api/v2/task/8677t70vw";

    //     $response = Http::withHeaders([
    //         "Authorization" => 'pk_26343077_E01A7RDPS0EA11BG6B7TI2A1T82L4WI5',
    //         "Content-Type" => "application/json"
    //     ])->get($baseUrl);
    //     \Log::info(['ClickUp' => $response->json()]);
    //     return $response->json();

    $taskId = "86a18bw8u";
    $query = array(
    "custom_task_ids" => "false",
    "include_subtasks" => "true",
    "include_markdown_description" => "false"
    );

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "Authorization: pk_38254709_31XI582SYM6HN73D7ZZNAF17B51KI1Y2"
        ],
        CURLOPT_URL => "https://api.clickup.com/api/v2/task/" . $taskId . "?" . http_build_query($query),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        echo "cURL Error #:" . $error;
    } else {
        return $response;
        // return response()->json($response);
    }
})->name('home');

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/generate-sow', [SoWGeneratorController::class, 'generate'])->name('generate.sow');
Route::get('/sows', [SoWGeneratorController::class, 'index'])->name('generate.index');
Route::get('/view-sow/{id}', [SoWGeneratorController::class, 'view'])->name('sow.view');

// Order Reorder
Route::get('/order', function () {

    $newItem = ["id" => 2, "name" => "Service 2", "order" => 4]; // Existing 2
    $orderManager = new ModelOrderManagerService(Services::class);
    // $newItem = ["name" => "Service 5", "order" => 5];
    $orderManager->addOrUpdateItem($newItem);

    // if(isset($newItem['id'])){
    //     $service = Services::find($newItem['id']);

    //     if($service->order > $newItem['order']){
    //         $shiftRight = Services::where('order', '>=', $newItem['order'])->where('order', '<', $service->order)->get();
    //         if($shiftRight){
    //             foreach ($shiftRight as $key => $right) {
    //                 $right->order = $right->order+1;
    //                 $right->save();
    //             }
    //         }
    //     }
    //     else{
    //         $shiftLeft = Services::where('order', '<=', $newItem['order'])->where('order', '>', $service->order)->get();
    //         if($shiftLeft){
    //             foreach ($shiftLeft as $key => $left) {
    //                 $left->order = $left->order-1;
    //                 $left->save();
    //             }
    //         }
    //     }

    //     $service->order = $newItem['order'];
    //     $service->save();
    // }

    // else{
    //     $existingData = Services::where('order', '>=', $newItem['order'])->get();

    //     if($existingData){
    //         foreach ($existingData as $key => $exist) {
    //             $exist->order = $exist->order+1;
    //             $exist->save();
    //         }
    //     }

    //     Services::create($newItem);
    // }

    $data = Services::orderBy('order')->get();

    return response()->json($data);


});


require __DIR__.'/auth.php';
