<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
class PayPalService
{
    protected $clientId;
    protected $secret;
    protected $baseUrl;

    public function __construct()
    {
        $this->clientId = config('paypal.paypal.client_id');
        $this->secret = config('paypal.paypal.secret');
        $this->baseUrl = config('paypal.paypal.mode') === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    public function getAccessToken()
    {
        $response = Http::withBasicAuth($this->clientId, $this->secret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        return $response->json()['access_token'];
    }

    public function createOrder($amount)
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->post("{$this->baseUrl}/v2/checkout/orders", [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => $amount,
                ],
            ]],
            'application_context' => [
                'return_url' => url('api/paypal/success-order'),
                'cancel_url' => url('api/paypal/cancel-order'),
            ]
        ]);

        return $response->json();
    }

    public function captureOrder($orderId){
        $token = $this->getAccessToken();
        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json',
        ])
        ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture", (object)[]);

        return $response->json();
    }

}
?>