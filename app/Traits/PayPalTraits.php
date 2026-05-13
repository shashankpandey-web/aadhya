<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

trait PayPalTraits
{

    public function paypal_indent_create($request, $wallet_id)
    {

        $totalAmount     = $request->amount;
        $authResponse    = Http::asForm()->withBasicAuth(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'))
            ->post(env('PAYPAL_BASE_URL') . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$authResponse->ok()) {
            return response()->json(['error' => 'Failed to obtain access token'], 500);
        }

        $accessToken = $authResponse->json()['access_token'];

        $orderResponse = Http::withToken($accessToken)
            ->post(env('PAYPAL_BASE_URL') . '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format($totalAmount, 2, '.', ''),
                        ],
                        'description' => "Order number $wallet_id",
                        'invoice_id' => $wallet_id,
                    ],
                ],
            ]);
        $orderResponse2 = json_decode($orderResponse);
        return $orderResponse2;
    }


    public function paypal_payout($getDetail)
    {

        $authResponse    = Http::asForm()->withBasicAuth(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'))
            ->post(env('PAYPAL_BASE_URL') . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$authResponse->ok()) {
            return response()->json(['error' => 'Failed to obtain access token'], 500);
        }

        $accessToken = $authResponse->json()['access_token'];

        

        $payload = [
            "sender_batch_header" => [
                "sender_batch_id" => "Payouts_" . now()->timestamp,
                "email_subject" => "You have a payout!",
                "email_message" => "You have received a payout! Thanks for using our service!",
            ],
            "items" => [
                [
                    "recipient_type" => "EMAIL",
                    "amount" => [
                        // 'value' => $getDetail['amount'],
                        'value' => "10",
                        "currency" => "USD",
                    ],
                    'receiver' => 'sb-pa6ob28072528@business.example.com',

                ],
            ],
        ];


        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])->post(env('PAYPAL_BASE_URL') . '/v1/payments/payouts', $payload);

        $orderResponse2 = json_decode($response);
        
        return $orderResponse2;
    }
}
