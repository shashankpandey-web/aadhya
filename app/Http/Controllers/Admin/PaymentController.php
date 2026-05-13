<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Languages;
use App\Models\Countries;
use App\Models\States;
use App\Models\City;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Traits\CashfreeTraits;


class PaymentController extends Controller
{
    //

    use CashfreeTraits;
    public function success(Request $request, $payment_id)
    {
        $common                 = array();
        $common['title']        = 'Payment Success';
        $common['main_menu']    = 'Payment Success';
        $common['submain_menu'] = 'Payment Success';
        $payment_data = [];
        $payment_data['payment_status'] = 'SUCCESS';
        $payment_data['message'] = 'Payment Successfully';
        return view('payment_status', compact('common', 'payment_data'));
    }

    public function cancel(Request $request, $payment_id)
    {
        $common                 = array();
        $common['title']        = 'Payment Cancel';
        $common['main_menu']    = 'Payment Cancel';
        $common['submain_menu'] = 'Payment Cancel';
        $payment_data = [];
        $payment_data['payment_status'] = 'CANCELLED';
        $payment_data['message'] = 'Payment Cancel';
        return view('payment_status', compact('common', 'payment_data'));
    }
}
