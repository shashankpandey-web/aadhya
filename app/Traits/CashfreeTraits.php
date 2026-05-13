<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Account;
use Stripe\Person;
use Stripe\File as StripeFile;
use App\Models\City;
use App\Models\States;
use Stripe\Transfer;
use Stripe\Payout;

trait CashfreeTraits
{

    public function create_payout($getDetail)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $transfer = Transfer::create([
            'amount' => round($getDetail['amount']) * 100,
            'currency' => "usd",
            'destination' => $getDetail['stripe_acc_id'],
            'transfer_group' => '#BR001',
        ]);

        $payout = Payout::create([
            'amount' => round($getDetail['amount']) * 100,
            'currency' => "usd",
            'method' => 'standard',
            'destination' => $getDetail['stripe_bank_id'],
        ], [
            'stripe_account' => $getDetail['stripe_acc_id'],
        ]);

        return $payout;
    }


    public function create_payment_link($request, $wallet_id)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $totalAmount     = $request->amount;
        $Auth            = Auth::guard('api')->user();
        $linkId          = mt_rand(100000, 999999) . '_' . $Auth->id;

        $payment_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => "USD",
                    'product_data' => [
                        'name' => 'Complete payment',
                    ],
                    'unit_amount' => intval($totalAmount) * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => env('APP_URL') . 'app.secure.payment.status.success/' . encrypt($wallet_id),
            'cancel_url' => env('APP_URL') . 'app.secure.payment.status.cancel/' . encrypt($wallet_id),
            'metadata' => [
                'name' => isset($Auth->full_name) ? $Auth->full_name : '',
                'email' => isset($Auth->email) ? $Auth->email : '',
                'phone' => isset($Auth->phone_number) ? $Auth->phone_number : '',
                'wallet_id' =>  $wallet_id,
                'phone_code' => '91',
            ],
            'expires_at' => time() + 1800,
        ]);
        return $payment_session;
    }



    public function create_stripe_account($request, $body)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $identy_proff_image  = '';
        $city_name  = '';
        $state_name = '';

        $City = City::where('id', $request->city)->first();
        if ($City) {
            $city_name = $City['name'];
        }

        $States = States::where('id', $request->state)->first();
        if ($States) {
            $state_name = $States['name'];
        }
        $country_short = 'US';

        $personData = [
            'first_name' => isset($request->first_name) ? $request->first_name : '',
            'last_name'  => isset($request->last_name) ? $request->last_name : '',
            'email'      => isset($request->email) ? $request->email : '',
            'address' => [
                'city'        => $city_name,
                'line1'       => isset($request->address) ? $request->address : '',
                'postal_code' => isset($request->postal_code) ? $request->postal_code : '',
                'state'       => $state_name,
            ],
            'dob' => [
                'day'   => date('d', strtotime($request->date_of_birth)),
                'month' => date('m', strtotime($request->date_of_birth)),
                'year'  => date('Y', strtotime($request->date_of_birth)),
            ],
            'phone'        => isset($request->phone_number) ? $request->phone_number : '',
            'relationship' => [
                'title'     => 'CEO',
                'director'  => true,
                'executive' => true,
                'owner'     => true,
                'representative'    => true,
                'percent_ownership' => 100,
            ],
            'ssn_last_4' => isset($request->ssn_last_4) ? $request->ssn_last_4 : '0000'
        ];

        if ($request->hasFile('identity_document')) {
            $file       = $request->file('identity_document');
            $filePath   = $file->getPathname();
            $fileName   = $file->getClientOriginalName();
            $path       = 'uploads/advisor_identy';
            $imagePath  = image_upload($request->file('identity_document'), $path);
            $identy_proff_image  = $imagePath;

            $stripeFile = StripeFile::create([
                'purpose' => 'identity_document',
                'file'    => fopen(public_path('uploads/advisor_identy/' . $imagePath), 'r'),
            ]);

            $personData['verification'] = [
                'document' => ['front' => $stripeFile->id],
            ];
        }

        $accountObj = [
            'business_type' => 'individual',
            'email' => isset($request->email) ? $request->email  : '',
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
            'tos_acceptance' => [
                'date' => time(),
                'ip' => $request->ip(),
            ],
            'business_profile' => [
                'mcc' => '5399',
                'url' => env('APP_URL'),
            ],
            'company' => [
                'address' => [
                    'city' => $city_name,
                    'line1' => isset($request->address) ? $request->address  : '',
                    'postal_code' => isset($request->postal_code) ? $request->postal_code  : '',
                    'state' => $state_name,
                ],
                'name' => isset($request->first_name) ? $request->first_name  : '',
                'phone' => isset($request->phone_number) ? $request->phone_number  : '',
                'tax_id' => isset($request->tax_id) ? $request->vat_id  : '',
                'vat_id' => $request->vat_id,
                'owners_provided' => true,
                'directors_provided' => true,
                'executives_provided' => true,
            ],
            'settings' => [
                'payments' => [
                    'statement_descriptor' => 'Addya',
                ],
            ],
        ];





        // if ($body['fileIsChanged']['company'] ?? false) {
        //     $accountObj['company']['verification'] = [
        //         'document' => ['front' => $body['stripe_identity_company_document_id']],
        //     ];
        // }

        if (isset($body['stripe_acc_id'])) {
            $account = Account::update($body['stripe_acc_id'], $accountObj);

            if (isset($body['stripe_person_id'])) {
                $representative = Account::updatePerson($account->id,$body['stripe_person_id'], $personData);
            } else {
                $representative = Account::createPerson($account->id,$personData);
            }
        } else {
            $accountObj['type']    = 'custom';
            $accountObj['country'] = $country_short;
            $account               = Account::create($accountObj);
            $representative        = Account::createPerson($account->id, $personData);;
        }

        return  [
            'stripe_acc_id' => $account->id,
            'stripe_person_id' => $representative->id,
            // 'stripe_bank_id' => $stripe_bank_id,
            'identy_proff_image' => $identy_proff_image,
        ];
    }


    public function addStripeBankDetail($request, $fields,$stripe_account_id,$stripe_bank_id ='')
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        extract($request->only(array_keys($fields)));
        $countryShort  = 'US';
        $currencyShort = "usd";

        $bankData = [
            'object' => 'bank_account',
            'country' => $countryShort,
            'currency' => $currencyShort,
            'account_holder_name' => $account_holder_name,
            'account_holder_type' => 'company',
            'account_number' => $account_number,
            'routing_number' => $routing_number,
        ];

        if ($stripe_bank_id) {
            $bankAccount = Account::updateExternalAccount(
                $stripe_account_id,
                $stripe_bank_id,
                [
                    'account_holder_name' => $account_holder_name,
                    'account_holder_type' => 'company',
                ]
            );
        } else {
            $bankAccount = Account::createExternalAccount(
                $stripe_account_id,
                [
                    'external_account' => $bankData,
                ]
            );
        }
        if ($bankAccount) {
            return $bankAccount;
        }
        return false;
    }


    public function deleteBankAccount($stripe_account_id, $stripe_bank_id)
    {
        
            // Set the API key
            Stripe::setApiKey(env('STRIPE_SECRET'));

            
            $deletedBankAccount = Account::deleteExternalAccount(
                $stripe_account_id,
                $stripe_bank_id
            );

            return $deletedBankAccount;
        
    }

    public function advisor_payout($getDetail)
    {


        $payout_data = [
            'beneficiary_details' => [
                'beneficiary_id' => $getDetail['beneficiary_id'],
            ],
            'transfer_id' => $getDetail['wallet_id'] . '_' . $getDetail['customer_id'] . '_' . mt_rand(100000, 999999),
            'transfer_amount' => round($getDetail['amount']),
            'transfer_currency' => 'INR',
            'transfer_mode' => 'banktransfer',
        ];

        $headers = [
            'x-client-id' => config('cashfree.CASHFREE_PAYOUT_CLIENT_ID'),
            'x-client-secret' => config('cashfree.CASHFREE_PAYOUT_CLIENT_SECRET'),
            'Content-Type' => 'application/json',
            'x-api-version' => config('cashfree.payout_api_version'),
        ];

        $response = Http::withHeaders($headers)->post(config('cashfree.create_payouts'), $payout_data);
        return $response->json();
    }
}
