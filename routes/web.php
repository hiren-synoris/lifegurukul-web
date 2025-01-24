<?php

use App\Http\Controllers\AppStoreController;
use App\Models\States;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/* test
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/ios', [AppStoreController::class, "index"]);

Route::get('/timezone', function () {
    echo date_default_timezone_get();
    echo "</br>";
    echo date('Y-m-d H:i:s');
    exit;
});

Route::get('/queue', function () {
    shell_exec('sudo apt-get install -y supervisor 2>&1');
    shell_exec('sudo systemctl start supervisor 2>&1');
    shell_exec('sudo systemctl stop supervisor 2>&1');

    Artisan::call('optimize:clear');
    return "done";
});

Route::get('/notify', function (Request $request) {

    $credentialsFilePath = storage_path('keys/life-gurukul-v2-firebase-adminsdk-y7ck7-05444ec86a.json');
    $client = new \Google_Client();
    $client->setAuthConfig($credentialsFilePath);
    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    $client->refreshTokenWithAssertion();
    $token = $client->getAccessToken();
    $token['access_token'];
    //dd($token['access_token']);
    $projectID = 'life-gurukul-v2';
    $notify_data = [
        'message' => [
            "token" => $_GET["token"],
            "notification" => [
                "title" => (string) "Learner",
                "body" => (string) "My name is lifegurukul",
            ],
            'data' => [
                "title" => (string) "Learner",
                "body" => (string) "My name is lifegurukul",
            ]
        ],
    ];

    $dataString = json_encode($notify_data);
    $apiurl = 'https://fcm.googleapis.com/v1/projects/' . $projectID . '/messages:send';
    $response = Http::withToken($token['access_token'])
        ->withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->post($apiurl, $notify_data);
    $res = $response->json();

    if ($response->successful()) {
        dd($res);
    } else {
        dd($response->body());
    }

    return "done";
});

Route::get('/send-test-mail', function () {
    try {
        // Mail::to('saifmsp7@gmail.com')->send(
        //     ['message' => [
        //         'from_email' => 'inoreply@devlifegurukul.com',
        //         // 'to' => collect($email->getTo())->map(function ($email) {
        //         //     return ['email' => $email->getAddress(), 'type' => 'to'];
        //         // })->all(),
        //         'subject' => "test",
        //         'text' => "sdfsdf",
        //     ]]
        // );

        // Mail::raw('Hi, welcome user!', function ($message) {
        //     $message->to("saifmsp7@gmail.com")
        //         ->subject("test");
        // });

        return "Test mail sent successfully!";
    } catch (\Exception $e) {
        return "Failed to send test mail. Error: " . $e->getMessage();
    }
});
Route::get('/test', function () {

    $baseUrl = 'https://api.appstoreconnect.apple.com';
    $credentialsFilePath = storage_path('keys/AuthKey_5JK79XMBSY.p8');
    $privateKeyPath = $credentialsFilePath;
    $keyId = "5JK79XMBSY";
    $ssl ="d4fc2edf-4504-4a7d-9ddb-4a84651d1407";
    $productId = "1566107921";

    $privateKey = file_get_contents($privateKeyPath);


    $token = [
        'iss' => $ssl,
        'iat' => time(),
        'exp' => time() + (60 * 20),
        'aud' => 'appstoreconnect-v1',
    ];

    $jwt = JWT::encode($token, $privateKey, 'ES256', $keyId);

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $jwt,
        'Content-Type' => 'application/json',
    ])->get($baseUrl . "/v1/apps/$productId/inAppPurchasesV2");

    $apps = $response->json();

    dd($apps);

    $inAppPurchases = $apps['data'] ?? [];

    return $inAppPurchases;

    Artisan::call('video-finished-earn-coin');
    Artisan::call('course-finished-earn-coin');
    return "done";
});
Route::get('/trigger-razorpay-sync', function () {
Artisan::call('app:get-failed-and-authorized');
    return "done";
});

Route::get('/update', function () {

    $ids = ["38", "231", "232","4"];
    $store_states_ids = collect([]);
    $store_cities_ids = collect([]);

    foreach ($ids as $id) {
        $states = States::where("country_id", $id)->get();
        foreach ($states as $states_id) {
            $store_states_ids = $store_states_ids->merge($states_id->id);
        }
    }
    States::whereIn('id', $store_states_ids)->update(["country_id" => "231"]);

    // \DB::table("user_courses")->whereNull("transaction_id")->where("order_status", 1)->update(["order_status" => 4]);

    return "done";
});

//<small class="text-gray">(jpg, svg, jpeg, png)</small>
/**
 * Front End Route Start From Here
 **/
// Route::group(['middleware' => 'prevent-back-history'],function(){
//     Auth::routes();
// Route::get('/home', 'HomeController@index');
// });
// Route::group(['middleware' => 'prevent-back-history'], function () {

// Include admin routes
include __DIR__ . '/front_route.php';
include __DIR__ . '/admin_route.php';

Route::get('/check-server-status', function () {
    $node_server_url = env('NODE_SERVER_URL');
    $serverStatus = "offline"; // Default status

    // Make a request to the Node server
    try {
        $response = Http::get($node_server_url . '/node-status'); // Adjust the URL based on your Node server configuration
        // dd($response);
        if ($response->status() === 200) {
            $serverStatus = "online";
        }
    } catch (\Illuminate\Http\Client\ConnectionException $exception) {
        $serverStatus = "offline";
    }

    return response()->json(['status' => $serverStatus]);
})->name('server-status');

// Route::get('/test-email-template', [App\Http\Controllers\front\SmsController::class, 'testEmailTemplate']);
