<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerWallet;
use App\Models\Logs;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

use Stripe\Stripe;
use Stripe\Webhook;


class WebhookController extends Controller
{

    public function handleStripePayout(Request $request)
    {
        
            $event = $request->all();
            // \Log::info("eventeventevent",$event);
            if (isset($event['data']['object'])) {
                $payoutPaid = $event['data']['object'];
                if (isset($payoutPaid['id'])) {
                    $transferId = $payoutPaid['id'];
                    $getCustomerWallet = CustomerWallet::where('payout_id', $transferId)->first();
                    // Handle the event
                    if($getCustomerWallet){
                        switch ($event['type']) {
                            case 'payout.paid':
                                if ($payoutPaid && $payoutPaid['status'] == 'paid') {
                                    $getCustomerWallet->withdrawal_status = 'TRANSFER_SUCCESS';
                                    $getCustomerWallet->save();
                                }
                                break;
                            case 'payout.failed':
                                $getCustomerWallet->withdrawal_status = 'TRANSFER_FAILED';
                                $getCustomerWallet->save();
                                break;
                            case 'payout.canceled':
                                $getCustomerWallet->withdrawal_status = 'TRANSFER_CANCELED';
                                $getCustomerWallet->save();
                                break;
                            default:
                                $getCustomerWallet->withdrawal_status = 'PENDING';
                                $getCustomerWallet->save();
                                break;
                        }
                    }
                }
            }
            return true;
        
    }
    public function handleStripePayment(Request $request)
    {
        $event        = $request->all();

        if (isset($request->data['object']['metadata'])) {
            $request_meta = $request->data['object']['metadata'];
            $event_type   = $request->type;
            if (isset($request_meta['wallet_id'])) {
                $CustomerWalletid = $request_meta['wallet_id'];

                $getCustomerWallet                = CustomerWallet::where('id', $CustomerWalletid)->first();
                $getCustomerWallet->payment_json  = json_encode($event);
                if ($getCustomerWallet) {
                    switch ($event_type) {
                        case 'payment_intent.succeeded':
                            try {
                                if (isset($getCustomerWallet)) {
                                    $getCustomerWallet->status       = 'SUCCESS';
                                    $getCustomerWallet->save();
                                }
                            } catch (\Exception $e) {
                                return response()->json(['error' => $e->getMessage()], 500);
                            }
                            break;
                        case 'checkout.session.completed':
                            try {
                                if (isset($getCustomerWallet)) {
                                    $getCustomerWallet->status       = 'SUCCESS';
                                    $getCustomerWallet->save();
                                }
                            } catch (\Exception $e) {
                                return response()->json(['error' => $e->getMessage()], 500);
                            }
                            break;
                        case 'payment_intent.processing':
                            try {
                                if (isset($getCustomerWallet)) {
                                    $getCustomerWallet->status       = 'PROCESSING';
                                    $getCustomerWallet->save();
                                }
                            } catch (\Exception $e) {
                                return response()->json(['success' => false, 'error' => $e->getMessage()]);
                            }
                            break;
                        case 'payment_intent.payment_failed':
                            try {
                                if (isset($getCustomerWallet)) {
                                    $getCustomerWallet->status       = 'FAILED';
                                    $getCustomerWallet->save();
                                }
                            } catch (\Exception $e) {
                                return response()->json(['success' => false, 'error' => $e->getMessage()]);
                            }
                            break;
                        case 'payment_intent.canceled':
                            try {
                                if (isset($getCustomerWallet)) {
                                    $getCustomerWallet->status       = 'CANCELED';
                                    $getCustomerWallet->save();
                                }
                            } catch (\Exception $e) {
                                return response()->json(['error' => $e->getMessage()], 500);
                            }
                            break;
                        default:
                            if (isset($getCustomerWallet)) {
                                $getCustomerWallet->status       = 'PENDING';
                                $getCustomerWallet->save();
                            }
                            break;
                    };
                }
            }
        }
        return true;
    }

    public function checkPaymentWebhook(Request $request, $wallet_id)
    {
        $body = $request->all();

        if (!empty($body['data']) && isset($body['data'])) {
            $link_status = isset($body['type']) ? $body['type'] : null;
            $CustomerWalletid  = checkDecrypt($wallet_id);

            $getCustomerWallet               = CustomerWallet::where('id', $CustomerWalletid)->first();
            $getCustomerWallet->payment_json = \json_encode($body);
            switch ($link_status) {
                case 'PAYMENT_SUCCESS_WEBHOOK':
                    $getCustomerWallet->status       = 'SUCCESS';
                    break;
                case 'PAYMENT_FAILED_WEBHOOK':
                    $getCustomerWallet->status       = 'FAILED';

                    break;
                case 'PAYMENT_USER_DROPPED_WEBHOOK':
                    $getCustomerWallet->status       = 'USER_DROPPED';
                    break;
                case 'PAYMENT_LINK_EVENT':
                    if (isset($body['data']['link_status'])) {
                        if ($body['data']['link_status'] == 'PAID') {
                            $getCustomerWallet->status       = 'SUCCESS';
                        }
                    }
                    break;
                default:
                    $getCustomerWallet->status       = 'PENDING';
                    break;
            }
            $getCustomerWallet->save();
            return true;
        }
    }

    public function checkPayoutWebhook(Request $request)
    {
        $body = $request->all();
        if (!empty($body['data']['transfer_id']) && isset($body['data']['transfer_id'])) {
            $transferId        = $body['data']['transfer_id'];
            $getCustomerWallet = CustomerWallet::where('payment_json->transfer_id', $transferId)->first();
            if ($getCustomerWallet) {
                if (isset($body['type'])) {
                    $getCustomerWallet->payment_json = \json_encode($body);
                    if ($body['type'] == 'TRANSFER_SUCCESS') {
                        $getCustomerWallet->withdrawal_status = 'TRANSFER_SUCCESS';
                    }
                    $getCustomerWallet->save();
                }
            }
        }
        return true;
    }
}
