<?php

namespace App\Console\Commands;

use Firebase\JWT\JWT;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\RenewingSubscriptions;

class RenewingSubscriptionsCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:renewing-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $baseUrl = 'https://api.appstoreconnect.apple.com';
        $credentialsFilePath = storage_path('keys/AuthKey_5JK79XMBSY.p8');
        $privateKeyPath = $credentialsFilePath;
        $keyId =  config('in_app_purchase.keyId');
        $ssl = config('in_app_purchase.ssl');
        $productId = config('in_app_purchase.product_id');

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

        $inAppPurchases = $apps['data'] ?? [];


        $renewingSubscriptions = RenewingSubscriptions::get();

        foreach ($renewingSubscriptions as $subscription) {
            $subscription->delete();
        }

        $results = [];
        foreach ($inAppPurchases as $inAppPurchase) {

            // if($inAppPurchase['attributes']['state']=="APPROVED") {
                $details = [
                    'name' => $inAppPurchase['attributes']['name'],
                    'productId' => $inAppPurchase['attributes']['productId'],
                    'inAppPurchaseType' => $inAppPurchase['attributes']['inAppPurchaseType'],
                    'state' => $inAppPurchase['attributes']['state'],
                    'familySharable' => $inAppPurchase['attributes']['familySharable'],
                ];
                RenewingSubscriptions::create($details);
            }

        // }

        return Command::SUCCESS;
    }
}
