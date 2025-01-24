<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

class AppStoreController extends Controller
{
    protected $baseUrl = 'https://api.appstoreconnect.apple.com';
    protected $privateKeyPath;
    protected $keyId;
    protected $teamId;

    public function __construct()
    {
        $credentialsFilePath = storage_path('keys/AuthKey_5JK79XMBSY.p8');
        dd($credentialsFilePath);
        $this->privateKeyPath = $credentialsFilePath;
        $this->keyId = '5JK79XMBSY';
        $this->teamId = '28VF6PALW2';
    }

    protected function generateJwtToken()
    {

        //     DD("");

        $privateKey = file_get_contents($this->privateKeyPath);

        $token = [
            'iss' => $this->teamId,
            'iat' => time(),
            'exp' => time() + (60 * 20),
            'aud' => 'appstoreconnect-v1',
        ];

        return JWT::encode($token, $privateKey, 'ES256', $this->keyId);
    }

    public function index()
    {
        // $node_server = env('NODE_SERVER_URL');
        // echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.3.2/socket.io.min.js"></script>';
        // // Echo out the JavaScript with the variable embedded
        // echo "<script>
        //     const socket_url = '{$node_server}';
        //     const socket = io(socket_url);
        //     alert(socket_url);
        //     socket.on('connect', function() {
        //         console.log('Socket is running');
        //     });
        // </script>";

        // // Stop further execution to avoid additional output
        // exit;

        // return response($script)->header('Content-Type', 'application/javascript');

        $token = $this->generateJwtToken();
        // $response = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . $token,
        //     'Content-Type' => 'application/json',
        // ])->get($this->baseUrl . '/v1/apps/1566107921/inAppPurchasesV2', [
        //     'limit' => 10,
        // ]);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->get($this->baseUrl . '/v1/apps');

        $apps = $response->json();
        dd($apps);
        $inAppPurchases = $apps['data'] ?? [];
        dd($inAppPurchases);
        $results = [];
        foreach ($inAppPurchases as $inAppPurchase) {
            $details = [
                'name' => $inAppPurchase['attributes']['name'],
                'productId' => $inAppPurchase['attributes']['productId'],
                'inAppPurchaseType' => $inAppPurchase['attributes']['inAppPurchaseType'],
                'state' => $inAppPurchase['attributes']['state'],
                'familySharable' => $inAppPurchase['attributes']['familySharable'],
            ];

            $pricing = $this->fetchPriceDetails($token, $inAppPurchase['id']);
            $details['pricing'] = $pricing;

            $results[] = $details;
        }

        return response()->json($results);
    }

    // public function index()
    // {
    //     $token = $this->generateJwtToken();
    //     $allInAppPurchases = [];
    //     $page = 1;
    //     $perPage = 50;
    //     $hasMorePages = true;

    //     while ($hasMorePages) {
    //         $response = Http::withHeaders([
    //             'Authorization' => 'Bearer ' . $token,
    //             'Content-Type' => 'application/json',
    //         ])->get($this->baseUrl . '/v1/apps/1566107921/inAppPurchasesV2', [
    //             'limit' =>200
    //         ]);

    //         if ($response->failed()) {
    //             // Log the error
    //             Log::error('API request failed', [
    //                 'status' => $response->status(),
    //                 'response' => $response->body(),
    //             ]);
    //             break;
    //         }

    //         $apps = $response->json();
    //         $inAppPurchases = $apps['data'] ?? [];
    //         $allInAppPurchases = array_merge($allInAppPurchases, $inAppPurchases);

    //         Log::info('Fetched records', [
    //             'page' => $page,
    //             'recordsFetched' => count($inAppPurchases),
    //             'totalFetchedSoFar' => count($allInAppPurchases),
    //         ]);

    //         // Assuming the response contains 'meta' with pagination info
    //         if (isset($apps['meta'])) {
    //             $totalRecords = $apps['meta']['total'];
    //             $hasMorePages = count($allInAppPurchases) < $totalRecords;
    //         } else {
    //             $hasMorePages = count($inAppPurchases) === $perPage;
    //         }

    //         if (!$hasMorePages || empty($inAppPurchases)) {
    //             break;
    //         }

    //         $page++;
    //     }

    //     return response()->json($allInAppPurchases);
    // }

    protected function fetchPriceDetails($token, $inAppPurchaseId)
    {
        $endpoint = $this->baseUrl . "/v1/inAppPurchasePriceSchedules/$inAppPurchaseId/manualPrices";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->get($endpoint);

        $priceDetails = $response->json();

        $prices = [];
        if (isset($priceDetails['data'])) {
            foreach ($priceDetails['data'] as $pricePoint) {
                $prices[] = [
                    'currency' => $pricePoint['attributes']['currency'] ?? 'N/A',
                    'amount' => $pricePoint['attributes']['price'] ?? 'N/A',
                ];
            }
        }

        return $prices;
    }
}
