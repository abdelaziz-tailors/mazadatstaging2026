<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelrPaymentService
{
    private $storeId;
    private $authKey;
    private $testMode;

    public function __construct()
    {
        $this->storeId = config('telr.store_id');
        $this->authKey = config('telr.auth_key');
        $this->testMode = config('telr.test_mode', true);
    }

    public function initiatePayment($amount, $currency, $returnUrl, $orderId)
    {
        $baseUrl = $this->testMode ? 'https://secure.telr.com/gateway/order.json' : 'https://secure.telr.com/gateway/order.json';

        $response = Http::post($baseUrl, [
            'ivp_method'  => 'create',
            'ivp_store'   => $this->storeId,
            'ivp_authkey' => $this->authKey,
            'ivp_amount'  => $amount,
            'ivp_currency' => $currency,
            'ivp_cart'    => $orderId,
            'ivp_test'    => $this->testMode ? 1 : 0,
            'return_auth' => $returnUrl . '?status=success',
            'return_decl' => $returnUrl . '?status=failure',
            'return_can'  => $returnUrl . '?status=cancel',
        ]);

        return $response->json();
    }
}
