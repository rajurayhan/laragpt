<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SoWGeneratorController;
use App\Http\Controllers\YelpFusionApiController;
use App\Models\CalendlyEvent;
use App\Models\Services;
use App\Services\ClickUpTaskManager;
use App\Services\ModelOrderManagerService;
use App\Services\SlackService;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    $data = Services::orderBy('order')->get();

    return response()->json($data);


});

// Yelp Lead API Integration Routes
Route::get('/yelp', function () {
    return 'Yelp Authorization';
});

Route::get('/yelp-oauth', [YelpFusionApiController::class, 'yelpInitOAuth'])->name('yelp.oauth.init');
Route::get('/yelp-oauth-callback', [YelpFusionApiController::class, 'yelpInitOAuthCallback'])->name('yelp.oauth.callback');

// Route::get('/yelp-auth-callback', function (Request $request) {
//     \Log::info(['Yelp Authorization' => $request->all()]);
//     return 'Yelp Authorization Callback';
// });

Route::match(['get', 'post'], '/yelp-leads-webhook', [YelpFusionApiController::class, 'receiveYelpWebhook']);
// Route::get('/yelp-leads-webhook', function (Request $request) {
//     \Log::info(['Yelp Lead' => $request->all()]);
//     return response()->json(['verification' => $request->verification]);
// });
Route::match(['get', 'post'], '/webhooks', [YelpFusionApiController::class, 'receiveYelpWebhook']);
// Route::match('get', '/what-converts', [YelpFusionApiController::class, 'getWhatConvertsLeads']);
// Route::match('get', '/what-convert-lead', [YelpFusionApiController::class, 'createWhatConvertLead']);
// Route::get('/webhooks', function (Request $request) {
//     \Log::info(['Yelp Lead' => $request->all()]);
//     return response()->json(['verification' => $request->verification]);
// });

Route::get('/yelp-responder', [YelpFusionApiController::class, 'testYelpResponder']);
Route::get('/yelp-business-subscribe', function (Request $request) {
    try {
        $businessID = 'SNa1ugk6DNIuvIPu8-AiGA';
        $appKey = env('YELP_APP_KEY');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $appKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.yelp.com/v3/businesses/subscriptions', [
            'business_ids' => [$businessID],
            'subscription_types' => ['WEBHOOK'],
        ]);
        return response()->json($response->json());
    } catch (RequestException $e) {
        // Handle Exception
    }
});
Route::get('/calendly-events', function () {
    return response()->json($data = CalendlyEvent::get());
});
Route::get('/send-slack-message', function () {
    $mentions = [
        'sean' => '<@U07LU19TW2C>',
        'naddie' => '<@U02Q207S1HP>',
        'kim' => '<@UA0G7DNN6>',
        'raju' => '<@U016C8R8486>',
        'josh' => '<@U08KLF2BG>',
    ];
    $channelId = 'C01SHC6KTK5';
    $message = "Hello ". $mentions['josh'] .", ". $mentions['kim'] .", ". $mentions['raju'] .", ". $mentions['naddie'] .", ". $mentions['sean'] ." This is Hive AI tryig to mention you all!";

    $slackService = new SlackService();

    $response = $slackService->sendMessageToChannel($channelId, $message);

    if ($response['ok']) {
        return response()->json(['status' => 'Message sent successfully']);
    } else {
        return response()->json(['status' => 'Failed to send message', 'error' => $response]);
    }
});
Route::get('/clickup-create-task', function () {
    $clickUpService = new ClickUpTaskManager();
    $listId = "182248192";
    $taskData = [
        "name" => "New Task Name From Hive Yelp Automation",
        "description" => "New Task Description From Hive Yelp Automation",
        "markdown_description" => "New Task Description From Hive Yelp Automation",
        "assignees" => [82155993],
        "archived" => false,
        "tags" => ["tag name 1"],
        "status" => "recently added",
        "priority" => 3,
        "due_date" => 1508369194377,
        "due_date_time" => false,
        "time_estimate" => 8640000,
        "start_date" => 1567780450202,
        "start_date_time" => false,
        "points" => 3,
        "notify_all" => true,
        "parent" => null,
        "links_to" => null,
        "check_required_custom_fields" => true,
        "custom_fields" => []
    ];

    try {
        $response = $clickUpService->createTask($listId, $taskData);
        return response()->json($response, 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
require __DIR__.'/auth.php';
