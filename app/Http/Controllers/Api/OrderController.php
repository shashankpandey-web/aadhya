<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mail;
use Image;
use Config;
use DB;
use App\Jobs\SendOrderReminder;
use App\Services\PayPalService;
use Illuminate\Support\Str;
use App\Models\Customers;
use App\Models\Countries;
use App\Models\States;
use App\Models\City;
use App\Models\Categories;
use App\Models\Availability;
use App\Models\Customerwishlist;
use App\Models\Advisorreviews;
use App\Models\CustomerCategories;
use App\Models\CustomerWallet;
use App\Models\CustomerAvailabilities;
use App\Models\Orders;
use App\Models\Chat;
use App\Models\Notifications;
use App\Traits\CashfreeTraits;
use App\Models\AdvisorBanks;
use App\Models\OrderQuestions;
use App\Models\AdvisorStripeAccount;
use App\Models\Logs;
use App\Models\Coupon;



class OrderController extends Controller
{

    use CashfreeTraits;

    public function place_order(Request $request)
    {

        try {


            /*$Logs                           = new Logs();
            $Logs->title              = 'place_order api request';
            $Logs->data              = json_encode($request->all());
            $Logs->save();*/

            // Validate the request
            $required_fields = [];
            $required_fields['device_id']                   = 'required';
            $required_fields['advisore_id']                 = 'required';
            $required_fields['advisore_availability_id']    = 'required';
            $required_fields['time']                        = 'required'; //deafult 1
            $validation = Validator::make($request->all(), $required_fields);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }
            $Auth             = Auth::guard('api')->user();
            // $get_availability = Availability::where(['id' => $request->advisore_availability_id])->first();
            $advisore_availability = CustomerAvailabilities::where(['id' => $request->advisore_availability_id, 'customer_id' => $request->advisore_id])->first();
            if ($advisore_availability) {
                $availability_id = $advisore_availability['availability_id'];
                $required_amount  = ceil($request->time) * $advisore_availability->charges;        // Per Minute

                if ($availability_id == config('avaiblityconfig.live_view_call') || config('avaiblityconfig.live_voice_call') == $availability_id) {
                    $seconds          = ceil($request->time);
                    $minites          = number_format($seconds / 60, 2);
                    $required_amount = $minites * $advisore_availability->charges;
                }


                // --- Coupon validation (optional) ---
                $coupon_discount_amount = 0;
                $applied_coupon_code    = null;

                if ($request->coupon_code) {
                    $Coupon = Coupon::where('code', trim($request->coupon_code))
                        ->where('status', 'Active')
                        ->whereNull('is_delete')
                        ->first();

                    if (!$Coupon) {
                        return response()->json(['status' => false, 'message' => 'Invalid coupon code'], 422);
                        die();
                    }

                    $now = now()->toDateTimeString();

                    if ($Coupon->start_date && $Coupon->start_date > $now) {
                        return response()->json(['status' => false, 'message' => 'Coupon is not yet active'], 422);
                        die();
                    }

                    if ($Coupon->expiry_date && $Coupon->expiry_date < $now) {
                        return response()->json(['status' => false, 'message' => 'Coupon has expired'], 422);
                        die();
                    }

                    if ($Coupon->usage_limit_per_coupon) {
                        $total_uses = Orders::where('coupon_code', $Coupon->code)->count();
                        if ($total_uses >= intval($Coupon->usage_limit_per_coupon)) {
                            return response()->json(['status' => false, 'message' => 'Coupon usage limit has been reached'], 422);
                            die();
                        }
                    }

                    if ($Coupon->usage_limit_per_user) {
                        $user_uses = Orders::where('coupon_code', $Coupon->code)
                            ->where('customer_id', $Auth->id)
                            ->count();
                        if ($user_uses >= intval($Coupon->usage_limit_per_user)) {
                            return response()->json(['status' => false, 'message' => 'You have already used this coupon'], 422);
                            die();
                        }
                    }

                    if ($Coupon->is_first_order_offer) {
                        $prior_orders = Orders::where('customer_id', $Auth->id)->count();
                        if ($prior_orders > 0) {
                            return response()->json(['status' => false, 'message' => 'This coupon is only valid for your first reading'], 422);
                            die();
                        }
                    }

                    if ($Coupon->type == 'percentage') {
                        $coupon_discount_amount = ($required_amount / 100) * floatval($Coupon->value);
                    } else {
                        $coupon_discount_amount = floatval($Coupon->value);
                    }

                    if ($Coupon->maximum_amount && $coupon_discount_amount > floatval($Coupon->maximum_amount)) {
                        $coupon_discount_amount = floatval($Coupon->maximum_amount);
                    }

                    $coupon_discount_amount = min($coupon_discount_amount, $required_amount);
                    $applied_coupon_code    = $Coupon->code;
                }

                $final_amount = $required_amount - $coupon_discount_amount;
                // --- End coupon validation ---

                $is_place_order   = false;
                $total_credite    = 0;
                $total_debit      = 0;
                $customer_wallets = CustomerWallet::where(['customer_id' => $Auth->id])->get();
                foreach ($customer_wallets as $key => $value) {
                    if ($value->type == 'Credit') {
                        $total_credite += $value->amount;
                    }
                    if ($value->type == 'Debit') {
                        $total_debit += $value->amount;
                    }
                }

                if ($total_credite >= 0 && $total_credite >= $total_debit) {
                    $available_amount = $total_credite - $total_debit;
                    if ($available_amount >= $final_amount) {
                        $is_place_order = true;
                    }
                }


                if ($is_place_order) {
                    $get_availability = Availability::where(['id' => $advisore_availability->availability_id])->first();
                    if ($get_availability) {


                        $SaveOrder                           = new Orders();
                        $SaveOrder->customer_id              = $Auth->id;
                        $SaveOrder->advisore_id              = $request->advisore_id;
                        $SaveOrder->order_availability_id = $advisore_availability['availability_id'];
                        $SaveOrder->advisore_availability_id = $request->advisore_availability_id;
                        $SaveOrder->availability_type        = $get_availability->title;
                        $SaveOrder->time                     = $request->time;
                        $SaveOrder->charges_type             = $advisore_availability->charges_type;
                        $SaveOrder->charges                  = $advisore_availability->charges;
                        $SaveOrder->total_charges            = $final_amount;
                        $SaveOrder->coupon_code              = $applied_coupon_code;
                        $SaveOrder->coupon_discount          = $coupon_discount_amount > 0 ? $coupon_discount_amount : null;
                        $SaveOrder->full_name                = isset($request->full_name) ? $request->full_name  : '';
                        $SaveOrder->dob                = isset($request->dob) ? $request->dob  : '';
                        $SaveOrder->gender                = isset($request->gender) ? $request->gender  : '';
                        $SaveOrder->message                  = isset($request->message) ? $request->message  : '';

                        if ($availability_id == config('avaiblityconfig.live_view_call') || config('avaiblityconfig.live_voice_call') == $availability_id) {
                            $SaveOrder->is_todo = 1;
                        }

                        
                        $SaveOrder->save();
                        $order_id = $SaveOrder->id;

                        if ($availability_id == config('avaiblityconfig.1_day_delivery') || config('avaiblityconfig.1_hour_delivery') == $availability_id) {
                            $getOrders = Orders::where('id', $order_id)->first();
                            if ($getOrders) {
                                $getOrders->is_todo                  = 0;
                                $getOrders->save();
                            }
                            $OrderQuestions           = new OrderQuestions();
                            $OrderQuestions->order_id = $order_id;
                            $OrderQuestions->type     = 'Question';
                            $OrderQuestions->detail   = isset($request->question) ? $request->question  : '';
                            $OrderQuestions->save();
                        }
                        // OrderQuestions

                        $SaveNotification              = new Notifications();
                        $SaveNotification->customer_id = $Auth->id;
                        $SaveNotification->title       = 'Your order has been placed successfully';
                        $SaveNotification->type        = 'order-placed';
                        $SaveNotification->order_id    = $SaveOrder->id;
                        $SaveNotification->save();

                        $SaveNotification              = new Notifications();
                        $SaveNotification->customer_id = $request->advisore_id;
                        $SaveNotification->title       = 'You have recieved new order';
                        $SaveNotification->type        = 'order-recieved';
                        $SaveNotification->order_id    = $SaveOrder->id;
                        $SaveNotification->save();

                        $CustomerWallet                           = new CustomerWallet();
                        $CustomerWallet->customer_id              = $Auth->id;
                        $CustomerWallet->amount                   = $final_amount;
                        $CustomerWallet->type                     = 'Debit';
                        $CustomerWallet->advisore_availability_id = $request->advisore_availability_id;
                        $CustomerWallet->order_id                 = $SaveOrder->id;
                        $CustomerWallet->save();

                        $AdvisorWallet                             = new CustomerWallet();
                        $AdvisorWallet->customer_id                = $request->advisore_id;
                        $AdvisorWallet->total_amount               = $final_amount;
                        $resposneData                              = get_calculate_amount($final_amount);
                        $AdvisorWallet->admin_commision_percentage = $resposneData['admin_commison_percentage'];
                        $AdvisorWallet->admin_commision_amount     = $resposneData['admin_commison_amount'];
                        $AdvisorWallet->amount                     = $resposneData['advisor_amount'];
                        $AdvisorWallet->advisore_availability_id   = $request->advisore_availability_id;
                        $AdvisorWallet->order_id                   = $SaveOrder->id;
                        $AdvisorWallet->save();


                        if ($availability_id == config('avaiblityconfig.1_day_delivery') || config('avaiblityconfig.1_hour_delivery') == $availability_id) {
                            $Customers = Customers::where(['id' => $request->advisore_id])->first();
                            if ($Customers) {
                                $data = array(); 
                                $data['name']  = $Customers->full_name;
                                $data['email'] = $Customers->email;
                                $data['phone_number']  = $Customers->phone_number;

                                $data['subject']       = 'You have received a new order.';
                                $data['template']      = 'email.order_adviser';
                                if ($data['template']) {
                                    Mail::send($data['template'], $data, function ($message) use ($data) {
                                        $message->to($data['email'], $data['name'])->subject($data['subject']);
                                    });
                                }
                            }
                            $Auth = Auth::guard('api')->user();
                            $data = array(); 
                            $data['name']  = $Auth->full_name;
                            $data['email'] = $Auth->email;
                            $data['phone_number']  = $Auth->phone_number;

                            $data['subject']       = 'Your order has been received!';
                            $data['template']      = 'email.order_customer';
                            Mail::send($data['template'], $data, function ($message) use ($data) {
                                $message->to($data['email'], $data['name'])->subject($data['subject']);
                            });

                            SendOrderReminder::dispatch($SaveOrder, '15-min')->delay(now()->addMinutes(15));

                            SendOrderReminder::dispatch($SaveOrder, '1-hour')->delay(now()->addHour());

                            SendOrderReminder::dispatch($SaveOrder, '6-hour')->delay(now()->addHours(6));

                            SendOrderReminder::dispatch($SaveOrder, '12-hour')->delay(now()->addHours(12));                            
                        }

                        return response()->json(['status' => true,'order_id' => $order_id, 'message' => 'Order Place Successfully']);
                        die();
                    } else {
                        return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
                        die();
                    }
                } else {
                    return response()->json(['status' => true, 'message' => 'Please Add Money in your wallet']);
                    die();
                }
            } else {
                return response()->json(['status' => false, 'message' => 'availability not found'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }


    public function place_order_advisor(Request $request)
    {

        try {
            // Validate the request
            $required_fields = [];
            $required_fields['device_id']                   = 'required';
            $required_fields['customer_id']                 = 'required';
            $required_fields['advisore_availability_id']    = 'required';
            $required_fields['time']                        = 'required'; //deafult 1
            $validation = Validator::make($request->all(), $required_fields);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }
            $Auth             = Auth::guard('api')->user();
            $advisore_availability = CustomerAvailabilities::where(['id' => $request->advisore_availability_id, 'customer_id' => $Auth->id])->first();
            if ($advisore_availability) {
                $availability_id = $advisore_availability['availability_id'];
                $required_amount  = ceil($request->time) * $advisore_availability->charges;        // Per Minute

                if ($availability_id == config('avaiblityconfig.live_view_call') || config('avaiblityconfig.live_voice_call') == $availability_id) {
                    $seconds          = ceil($request->time);
                    $minites          = number_format($seconds / 60, 2);
                    $required_amount = $minites * $advisore_availability->charges;
                }


                $is_place_order   = false;
                $total_credite    = 0;
                $total_debit      = 0;
                $customer_wallets = CustomerWallet::where(['customer_id' => $request->customer_id])->get();
                foreach ($customer_wallets as $key => $value) {
                    if ($value->type == 'Credit') {
                        $total_credite += $value->amount;
                    }
                    if ($value->type == 'Debit') {
                        $total_debit += $value->amount;
                    }
                }

                if ($total_credite >= 0 && $total_credite >= $total_debit) {
                    $available_amount = $total_credite - $total_debit;
                    if ($available_amount >= $required_amount) {
                        $is_place_order = true;
                    }
                }


                if ($is_place_order) {
                    $get_availability = Availability::where(['id' => $advisore_availability->availability_id])->first();
                    if ($get_availability) {              


                        $SaveOrder                           = new Orders();
                        $SaveOrder->customer_id              = $request->customer_id;
                        $SaveOrder->advisore_id              = $Auth->id;
                        $SaveOrder->order_availability_id = $advisore_availability['availability_id'];
                        $SaveOrder->advisore_availability_id = $request->advisore_availability_id;
                        $SaveOrder->availability_type        = $get_availability->title;
                        $SaveOrder->time                     = $request->time;
                        $SaveOrder->charges_type             = $advisore_availability->charges_type;
                        $SaveOrder->charges                  = $advisore_availability->charges;
                        $SaveOrder->total_charges            = $required_amount;
                        $SaveOrder->full_name                = isset($request->full_name) ? $request->full_name  : '';
                        $SaveOrder->dob                = isset($request->dob) ? $request->dob  : '';
                        $SaveOrder->gender                = isset($request->gender) ? $request->gender  : '';
                        $SaveOrder->message                  = isset($request->message) ? $request->message  : '';

                        if ($availability_id == config('avaiblityconfig.live_view_call') || config('avaiblityconfig.live_voice_call') == $availability_id) {
                            $SaveOrder->is_todo = 1;
                        }

                        
                        $SaveOrder->save();
                        $order_id = $SaveOrder->id;

                        if ($availability_id == config('avaiblityconfig.1_day_delivery') || config('avaiblityconfig.1_hour_delivery') == $availability_id) {
                            $getOrders = Orders::where('id', $order_id)->first();
                            if ($getOrders) {
                                $getOrders->is_todo                  = 0;
                                $getOrders->save();
                            }
                            $OrderQuestions           = new OrderQuestions();
                            $OrderQuestions->order_id = $order_id;
                            $OrderQuestions->type     = 'Question';
                            $OrderQuestions->detail   = isset($request->question) ? $request->question  : '';
                            $OrderQuestions->save();
                        }
                        // OrderQuestions

                        $SaveNotification              = new Notifications();
                        $SaveNotification->customer_id = $request->customer_id;
                        $SaveNotification->title       = 'Your order has been placed successfully';
                        $SaveNotification->type        = 'order-placed';
                        $SaveNotification->order_id    = $SaveOrder->id;
                        $SaveNotification->save();

                        $SaveNotification              = new Notifications();
                        $SaveNotification->customer_id = $Auth->id;
                        $SaveNotification->title       = 'You have recieved new order';
                        $SaveNotification->type        = 'order-recieved';
                        $SaveNotification->order_id    = $SaveOrder->id;
                        $SaveNotification->save();

                        $CustomerWallet                           = new CustomerWallet();
                        $CustomerWallet->customer_id              = $request->customer_id;
                        $CustomerWallet->amount                   = $required_amount;
                        $CustomerWallet->type                     = 'Debit';
                        $CustomerWallet->advisore_availability_id = $request->advisore_availability_id;
                        $CustomerWallet->order_id                 = $SaveOrder->id;
                        $CustomerWallet->save();

                        $amount                                    = $required_amount;
                        $AdvisorWallet                             = new CustomerWallet();
                        $AdvisorWallet->customer_id                = $Auth->id;
                        $AdvisorWallet->total_amount               = $amount;
                        $resposneData                              = get_calculate_amount($amount);
                        $AdvisorWallet->admin_commision_percentage = $resposneData['admin_commison_percentage'];
                        $AdvisorWallet->admin_commision_amount     = $resposneData['admin_commison_amount'];
                        $AdvisorWallet->amount                     = $resposneData['advisor_amount'];
                        $AdvisorWallet->advisore_availability_id   = $request->advisore_availability_id;
                        $AdvisorWallet->order_id                   = $SaveOrder->id;
                        $AdvisorWallet->save();

                        return response()->json(['status' => true,'order_id' => $order_id, 'message' => 'Order Place Successfully']);
                        die();
                    } else {
                        return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
                        die();
                    }
                } else {
                    return response()->json(['status' => true, 'message' => 'User do not have sufficient Money in wallet']);
                    die();
                }
            } else {
                return response()->json(['status' => false, 'message' => 'availability not found'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function place_extend(Request $request)
    {

        try {
            // Validate the request
            $required_fields = [];
            $required_fields['device_id']                   = 'required';
            $required_fields['order_id']                 = 'required';
            $required_fields['time']                        = 'required'; //deafult 1
            $validation = Validator::make($request->all(), $required_fields);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }
            $Auth             = Auth::guard('api')->user();

            $get_order = Orders::where('id', $request->order_id)->first();
            if ($get_order) {
                // code...
            }else{
                return response()->json($output);
            }

            $order_id = $request->order_id;

            $advisore_availability = CustomerAvailabilities::where(['id' => $get_order->advisore_availability_id, 'customer_id' => $get_order->advisore_id])->first();
            if ($advisore_availability) {
                $availability_id = $advisore_availability['availability_id'];
                $required_amount  = ceil($request->time) * $advisore_availability->charges;        // Per Minute

                if ($availability_id == config('avaiblityconfig.live_view_call') || config('avaiblityconfig.live_voice_call') == $availability_id) {
                    $seconds          = ceil($request->time);
                    $minites          = number_format($seconds / 60, 2);
                    $required_amount = $minites * $advisore_availability->charges;
                }


                $is_place_order   = false;
                $total_credite    = 0;
                $total_debit      = 0;
                $customer_wallets = CustomerWallet::where(['customer_id' => $Auth->id])->get();
                foreach ($customer_wallets as $key => $value) {
                    if ($value->type == 'Credit') {
                        $total_credite += $value->amount;
                    }
                    if ($value->type == 'Debit') {
                        $total_debit += $value->amount;
                    }
                }

                if ($total_credite >= 0 && $total_credite >= $total_debit) {
                    $available_amount = $total_credite - $total_debit;
                    if ($available_amount >= $required_amount) {
                        $is_place_order = true;
                    }
                }


                if ($is_place_order) {
                    $get_availability = Availability::where(['id' => $advisore_availability->availability_id])->first();
                    if ($get_availability) {

                        $CustomerWallet                           = new CustomerWallet();
                        $CustomerWallet->customer_id              = $Auth->id;
                        $CustomerWallet->amount                   = $required_amount;
                        $CustomerWallet->type                     = 'Debit';
                        $CustomerWallet->advisore_availability_id = $get_order->advisore_availability_id;
                        $CustomerWallet->order_id                 = $order_id;
                        $CustomerWallet->save();

                        $amount                                    = $required_amount;
                        $AdvisorWallet                             = new CustomerWallet();
                        $AdvisorWallet->customer_id                = $get_order->advisore_id;
                        $AdvisorWallet->total_amount               = $amount;
                        $resposneData                              = get_calculate_amount($amount);
                        $AdvisorWallet->admin_commision_percentage = $resposneData['admin_commison_percentage'];
                        $AdvisorWallet->admin_commision_amount     = $resposneData['admin_commison_amount'];
                        $AdvisorWallet->amount                     = $resposneData['advisor_amount'];
                        $AdvisorWallet->advisore_availability_id   = $get_order->advisore_availability_id;
                        $AdvisorWallet->order_id                   = $order_id;
                        $AdvisorWallet->save();

                        return response()->json(['status' => true,'order_id' => $order_id, 'message' => 'Order Place Successfully']);
                        die();
                    } else {
                        return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
                        die();
                    }
                } else {
                    return response()->json(['status' => true, 'message' => 'Please Add Money in your wallet']);
                    die();
                }
            } else {
                return response()->json(['status' => false, 'message' => 'availability not found'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

     public function createPaypalOrder(PayPalService $paypal, Request $request)
    {

        $output = array();
        $output['status'] = false;
        $output['message']      = 'Something went wrong';

        $required_fields              = [];
        $required_fields['device_id'] = 'required';
        $required_fields['amount']      = 'required';

        $validation                   = Validator::make($request->all(), $required_fields);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
            die();
        }

        $order = $paypal->createOrder($request->amount);
        //return response()->json($order);
        $approveLink = collect($order['links'])->firstWhere('rel', 'approve')['href'] ?? null;

        if ($approveLink) {
            $output = array();
            $output['status'] = true;
            $output['link'] = $approveLink;
            $output['message']  = 'Paypal Order Link';
        }
        return response()->json($output);
    }

    public function capturePaypalOrder(PayPalService $paypal, Request $request)
    {
        $output = array();
        $output['status'] = false;
        $output['message']      = 'Something went wrong';

        $required_fields              = [];
        $required_fields['device_id'] = 'required';
        $required_fields['paypal_token']      = 'required';

        $validation                   = Validator::make($request->all(), $required_fields);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
            die();
        }
        //echo "string ".$request->paypal_token; die;
        $capture = $paypal->captureOrder($request->paypal_token);
        return response()->json($capture);
    }

    public function createPaypalSuccess(PayPalService $paypal, Request $request)
    {
        /*$output = array();
        $output['status'] = false;
        $output['message']      = 'Paypal Success link';
        $output['data']      = $request->all();
        return response()->json($output);*/

        $request_all = array();
        $request_all['status'] = true;
        $request_all['message']      = 'Paypal Success link';
        $request_all['data']      = $request->all();
        return view('api.createPaypalSuccess', compact('request_all'));
    }

    public function createPaypalCancel(PayPalService $paypal, Request $request)
    {
        /*$output = array();
        $output['status'] = false;
        $output['message']      = 'Paypal cancel link';
        $output['data']      = $request->all();
        return response()->json($output);*/

        $request_all = array();
        $request_all['status'] = false;
        $request_all['message']      = 'Paypal Cancel link';
        $request_all['data']      = $request->all();
        return view('api.createPaypalSuccess', compact('request_all'));
    }


    


    public function validate_coupon(Request $request)
    {
        $Auth = Auth::guard('api')->user();

        $rules               = [];
        $rules['coupon_code'] = 'required';
        $validation          = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
            die();
        }

        $Coupon = Coupon::where('code', trim($request->coupon_code))
            ->where('status', 'Active')
            ->whereNull('is_delete')
            ->first();

        if (!$Coupon) {
            return response()->json(['status' => false, 'message' => 'Invalid coupon code'], 422);
            die();
        }

        $now = now()->toDateTimeString();

        if ($Coupon->start_date && $Coupon->start_date > $now) {
            return response()->json(['status' => false, 'message' => 'Coupon is not yet active'], 422);
            die();
        }

        if ($Coupon->expiry_date && $Coupon->expiry_date < $now) {
            return response()->json(['status' => false, 'message' => 'Coupon has expired'], 422);
            die();
        }

        if ($Coupon->usage_limit_per_coupon) {
            $total_uses = Orders::where('coupon_code', $Coupon->code)->count();
            if ($total_uses >= intval($Coupon->usage_limit_per_coupon)) {
                return response()->json(['status' => false, 'message' => 'Coupon usage limit has been reached'], 422);
                die();
            }
        }

        if ($Coupon->usage_limit_per_user) {
            $user_uses = Orders::where('coupon_code', $Coupon->code)
                ->where('customer_id', $Auth->id)
                ->count();
            if ($user_uses >= intval($Coupon->usage_limit_per_user)) {
                return response()->json(['status' => false, 'message' => 'You have already used this coupon'], 422);
                die();
            }
        }

        if ($Coupon->is_first_order_offer) {
            $prior_orders = Orders::where('customer_id', $Auth->id)->count();
            if ($prior_orders > 0) {
                return response()->json(['status' => false, 'message' => 'This coupon is only valid for your first reading'], 422);
                die();
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Coupon applied successfully',
            'data'    => [
                'coupon_code'    => $Coupon->code,
                'type'           => $Coupon->type,
                'value'          => floatval($Coupon->value),
                'maximum_amount' => $Coupon->maximum_amount ? floatval($Coupon->maximum_amount) : null,
            ],
        ]);
        die();
    }

    public function transactions(Request $request)
    {

        try {
            // Validate the request
            $required_fields              = [];
            $required_fields['device_id'] = 'required';
            // $required_fields['type']      = 'required';
            $validation                   = Validator::make($request->all(), $required_fields);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth             = Auth::guard('api')->user();
            $transaction_type = isset($request->type) ? $request->type : '';


            $setLimit     = config('apiconfig.records_per_page');
            $offset       = 0;
            if ($request->offset) {
                $offset   = $request->offset-1;
            }
            $limit        = $offset * $setLimit;

            $TransactionArrs = [];
            $customer_wallet = CustomerWallet::where(['customer_id' => $Auth->id]);
            if ($transaction_type) {
                if ($transaction_type == 'Withdrawal') {
                    $customer_wallet = $customer_wallet->where('is_withdrawal', 1)->where('type', 'Debit');
                } else {
                    $customer_wallet = $customer_wallet->where('type', $transaction_type);
                    if ($Auth->type == 'Customer') {
                        $customer_wallet = $customer_wallet->where('status', 'SUCCESS');
                    }
                }
            }

            $TotalCount = count($customer_wallet->get());
            $customer_wallet = $customer_wallet->orderBy('id', 'desc')->offset($limit)->take($setLimit)->get();

            if ($customer_wallet) {
                foreach ($customer_wallet as $key => $value) {
                    $TransactionArr                      = [];
                    $TransactionArr['id']                = $value->id;
                    $TransactionArr['amount']            = floatval($value->amount);
                    $TransactionArr['type']              = $value->type;
                    $TransactionArr['withdrawal_status'] = $value->withdrawal_status;
                    $TransactionArr['is_withdrawn']      = $value->is_withdrawal ? true : false;
                    $TransactionArr['created_at']        = $value->created_at;
                    $TransactionArr['customer_name']     = '';
                    $TransactionArr['email']             = '';
                    $TransactionArr['is_online'] = false;
                    $TransactionArr['is_busy'] = false;
                    $TransactionArr['title']        = 'Add wallet amount';
                    $TransactionArr['image']        = url('assets/images/wallet_img.png');

                    if ($value->is_withdrawal) {
                        $TransactionArr['title']        = 'Withdrawal amount';
                    }
                    if ($value['order_id']) {
                        $get_order = Orders::where('id', $value['order_id'])->first();
                        if ($get_order) {
                            if ($get_order['advisore_availability_id']) {
                                $CustomerAvailabilities = CustomerAvailabilities::where('id', $get_order['advisore_availability_id'])->first();
                                if ($CustomerAvailabilities) {
                                    $Availability = Availability::where('id', $CustomerAvailabilities['availability_id'])->first();
                                    if ($Availability) {
                                        $TransactionArr['image'] = url('uploads/availability/' . $Availability['image']);
                                        $TransactionArr['title'] = $Availability['title'];
                                    }
                                }
                            }
                            $getCustomer                         = Customers::where('id',$get_order->customer_id)->first();
                            if($getCustomer){
                                $TransactionArr['customer_name'] = $getCustomer->full_name;
                                $TransactionArr['is_online'] = ($getCustomer->is_online==1) ? true : false;
                                $TransactionArr['is_busy'] = ($getCustomer->is_busy==1) ? true : false;
                                $TransactionArr['email']         = $getCustomer->email;
                            }

                        }
                    }
                    $TransactionArrs[]              = $TransactionArr;
                }

                $page_count         = ceil($TotalCount / $setLimit);
                $data['status']     = true;
                $data['data']       = $TransactionArrs;
                $data['page_count'] = $page_count;
                $data['count']      = $TotalCount;
                $data['message']    = 'Transaction List';

                return response()->json($data);
            } else {
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function my_orders(Request $request)
    {

        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth = Auth::guard('api')->user();
            $customer = Customers::where(['id' => $Auth->id, 'status' => 'Active'])->whereNull('is_delete')->first();
            $where_arr = [];
            if ($customer->type == 'Advisor') {
                $where_arr['orders.advisore_id'] = $Auth->id;
            } else {
                $where_arr['orders.customer_id'] = $Auth->id;
            }


            $setLimit     = 10;
            $offset       = 0;
            if ($request->offset) {
                $offset   = $request->offset-1;
            }
            $limit        = $offset * $setLimit;


            $OrderArrs = [];
            $my_orders = Orders::where($where_arr);
            if ($request->availability_id) {
                $my_orders = $my_orders->select('orders.*', 'ca.availability_id')->leftJoin('customer_availabilities as ca', 'ca.id', '=', 'orders.advisore_availability_id')
                    ->leftJoin('availabilities as aa', 'aa.id', '=', 'ca.availability_id')
                    ->whereIn('aa.id', $request->availability_id);
            }

            $ordersCount = count($my_orders->get());

            $my_orders = $my_orders->orderBy('id', 'desc')->offset($limit)->take($setLimit)->get();
            //if ($my_orders) {
                foreach ($my_orders as $key => $value) {
                    $OrderArr                    = [];
                    $OrderArr['id']              = $value->id;
                    $OrderArr['availability_id'] = '';

                    $OrderArr['customer_id']        = $value->customer_id;
                    $OrderArr['advisore_id']        = $value->advisore_id;

                    $OrderArr['full_name']  = '';
                    $OrderArr['image'] = '';
                    $OrderArr['is_online'] = false;
                    $OrderArr['is_busy'] = false;
                    $get_advisor               = Customers::where(['id' => $value->advisore_id, 'status' => 'Active'])->whereNull('is_delete')->first();
                    if ($get_advisor) {
                        $OrderArr['full_name'] = $get_advisor->full_name;
                        $OrderArr['image']     = $get_advisor->image ? url('uploads/image/' . $get_advisor->image) : url('uploads/placeholder/dummy_image.png');
                        $OrderArr['is_online'] = ($get_advisor->is_online==1) ? true : false;
                        $OrderArr['is_busy'] = ($get_advisor->is_busy==1) ? true : false;
                    }
                    $OrderArr['availability_type']  = isset($value->availability_type) ? $value->availability_type : '';
                    $OrderArr['charges']            = floatval($value->charges);
                    $OrderArr['charges_type']       = $value->charges_type;
                    $OrderArr['time']               = $value->time;
                    $OrderArr['total_charges']      = floatval($value->total_charges);
                    $OrderArr['created_at']         = $value->created_at;
                    $OrderArr['availability_id']    = '';
                    $OrderArr['availability_image'] = '';
                    $OrderArr['customer_image']     = $customer->image ? url('uploads/image/' . $customer->image) : url('uploads/placeholder/dummy_image.png');
                    $OrderArr['availability_name']  = '';
                    $advisore_availability          = CustomerAvailabilities::where(['id' => $value->advisore_availability_id])->first();
                    if ($advisore_availability) {
                        $get_availability               = Availability::where(['id' => $advisore_availability['availability_id']])->first();
                        if ($get_availability) {
                            $OrderArr['availability_id']    = $get_availability['id'];
                            $OrderArr['availability_image'] = $get_availability['image'] ? url('uploads/availability/' . $get_availability['image']) : '';
                            $OrderArr['availability_name']  = $get_availability['title'] ? $get_availability['title'] : '';
                        }
                    }

                    if ($customer->type == 'Advisor') {
                        $total_amount = CustomerWallet::where(['order_id' => $value->id,'customer_id' => $Auth->id])->sum('total_amount');
                    }else{
                        $total_amount = CustomerWallet::where(['order_id' => $value->id,'customer_id' => $Auth->id])->sum('amount');
                    }
                    $OrderArr['total_amount'] = get_price_format($total_amount);

                    $is_unread_message = Chat::where(['order_id' => $value->id,'to' => $Auth->id,'is_seen' =>'0'])->count();
                    $OrderArr['is_unread_message'] = $is_unread_message;


                    $OrderArrs[]                   = $OrderArr;
                }
                //return response()->json(['status' => true, 'message' => 'My Order List', 'data' => $OrderArrs]);
                

                $page_count    = ceil($ordersCount / $setLimit);
                $order_count = $ordersCount;

                $output['status'] = true;
                $output['data'] = $OrderArrs;
                $output['page_count']    = $page_count;
                $output['order_count'] = $order_count;
                $output['message'] = translate('My Order List');
                return response()->json($output);
                die;
            // } else {
            //     return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
            // }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die;
        }
    }

    public function order_detail(Request $request)
    {

        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'order_id'   => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth            = Auth::guard('api')->user();
            $customer        = Customers::where(['id' => $Auth->id, 'status' => 'Active'])->whereNull('is_delete')->first();
            $where_arr       = [];
            $where_arr['id'] = $request->order_id;
            if ($customer->type == 'Advisor') {
                $where_arr['advisore_id'] = $Auth->id;
            } else {
                $where_arr['customer_id'] = $Auth->id;
            }

            $OrderArr = [];
            $get_order = Orders::where($where_arr)->first();
            if ($get_order) {
                $OrderArr['id']            = $get_order->id;
                $OrderArr['customer_id']   = $get_order->customer_id;
                $OrderArr['advisore_id']   = $get_order->advisore_id;
                $OrderArr['full_name']  = '';
                $OrderArr['is_online'] = false;
                $OrderArr['is_busy'] = false;
                $OrderArr['image'] = '';
                $get_advisor               = Customers::where(['id' => $get_order->advisore_id, 'status' => 'Active'])->whereNull('is_delete')->first();
                if ($get_advisor) {
                    $OrderArr['full_name'] = $get_advisor->full_name;
                    $OrderArr['image']     = $get_advisor->image ? url('uploads/image/' . $get_advisor->image) : url('uploads/placeholder/dummy_image.png');
                    $OrderArr['is_online'] = ($get_advisor->is_online==1) ? true : false;
                    $OrderArr['is_busy'] = ($get_advisor->is_busy==1) ? true : false;
                }
                $OrderArr['availability_type'] = $get_order->availability_type;
                $OrderArr['charges']           = floatval($get_order->charges);
                $OrderArr['charges_type']      = $get_order->charges_type;
                $OrderArr['time']              = $get_order->time;
                $OrderArr['order_full_name']   = $get_order->full_name ? $get_order->full_name : '';
                $OrderArr['dob']           = $get_order->dob   ? $get_order->dob   : '';
                $OrderArr['gender']           = $get_order->gender   ? $get_order->gender   : '';
                $OrderArr['message']           = $get_order->message   ? $get_order->message   : '';
                $OrderArr['total_charges']     = "$" . floatval($get_order->total_charges);
                $OrderArr['created_at']        = $get_order->created_at;
                $OrderArr['created_at']        = $get_order->created_at;

                $OrderArr['availability_id']    = '';
                $OrderArr['availability_image'] = '';
                $OrderArr['availability_name']  = '';
                $advisore_availability          = CustomerAvailabilities::where(['id' => $get_order->advisore_availability_id])->first();
                if ($advisore_availability) {
                    $get_availability               = Availability::where(['id' => $advisore_availability['availability_id']])->first();
                    if ($get_availability) {
                        $OrderArr['availability_id']    = $get_availability['id'];
                        $OrderArr['availability_image'] = $get_availability['image'] ? url('uploads/availability/' . $get_availability['image']) : '';
                        $OrderArr['availability_name']  = $get_availability['title'] ? $get_availability['title'] : '';
                    }
                }

                $OrderArr['orders_chat']       = [];
                $get_order_quesions            = OrderQuestions::where('order_id', $get_order->id)->orderBy('id', 'asc')->get();
                if (count($get_order_quesions) > 0) {
                    foreach ($get_order_quesions as $key => $value) {
                        $arr           = [];
                        $arr['id']   = $value['id'];
                        $arr['type']   = $value['type'];
                        $arr['detail'] = $value['detail'];
                        $arr['video']  = isset($value->video) ? get_image_upload_s3($value->video) : '';

                        $OrderArr['orders_chat'][] = $arr;
                    }
                }

                return response()->json(['status' => true, 'message' => 'Order Detail', 'data' => $OrderArr]);
                die();
            } else {
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
                die();
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function withdrawal(Request $request)
    {
        try {
            // Validate the request
            $err = [
                'device_id'         => 'required',
                'withdrawal_amount' => 'required',
                //'bank_id'           => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth  = Auth::guard('api')->user();
            
            /*$AdvisorStripeAccount = AdvisorStripeAccount::where('advisor_id',$Auth->id)->first();
            if(!$AdvisorStripeAccount){
                return response()->json(['status' => false, 'message' => 'Please add Bank Detail']);
            }
            if(!isset($AdvisorStripeAccount->stripe_acc_id)){
                return response()->json(['status' => false, 'message' => 'Please add Bank Detail']);
            }*/
            /*if(!isset($AdvisorStripeAccount->stripe_bank_id)){
                return response()->json(['status' => false, 'message' => 'Please add Bank Detail']);
            }*/

            /*$AdvisorBanks = AdvisorBanks::where('customer_id', $Auth->id)->where('id', $request->bank_id)->whereNull('is_delete')->first();
            if(!$AdvisorBanks){
                return response()->json(['status' => false, 'message' => 'Bank are Not exists']);
            }*/
            $request_withdrawal_amount = $request->withdrawal_amount;
            $total_amount              = CustomerWallet::getTotalAmount($Auth->id);


            if ($total_amount >= $request_withdrawal_amount) {
                $CustomerWallet                    = new CustomerWallet();
                $CustomerWallet->customer_id       = $Auth->id;
                $CustomerWallet->amount            = $request->withdrawal_amount;
                $CustomerWallet->type              = 'Debit';
                $CustomerWallet->withdrawal_status = 'PENDING';
                //$CustomerWallet->bank_id           = $AdvisorBanks->id;
                $CustomerWallet->is_withdrawal     = true;
                $CustomerWallet->save();

                $getCustomerWallet                 = CustomerWallet::where('id', $CustomerWallet->id)->first();
                $getCustomerWallet->request_number = "#AD" . $getCustomerWallet->id;
                $getCustomerWallet->save();

                $SaveNotification              = new Notifications();
                $SaveNotification->customer_id = $Auth->id;
                $SaveNotification->title       = 'Amount $' . $request->withdrawal_amount . ' Withdrawal successfully';
                $SaveNotification->type        = 'withdrawal';
                $SaveNotification->save();


                if ($Auth->email) {
                    $data = array(); 
                    $data['name']  = $Auth->full_name;
                    $data['email'] = $Auth->email;
                    $data['phone_number']  = $Auth->phone_number;

                    $data['subject']       = 'Your withdrawal request has been received ';
                    $data['template']      = 'email.withdrawal';
                    if ($data['template']) {
                        Mail::send($data['template'], $data, function ($message) use ($data) {
                            $message->to($data['email'], $data['name'])->subject($data['subject']);
                        });
                    }
                }

                return response()->json(['status' => true, 'message' => 'Withdrawal Successfully']);
            } else {
                $output['message'] = '';
                return response()->json(['status' => false, 'message' => 'You have ' . $total_amount . ' Amount only']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }


    public function get_adviser_wallet_detail(Request $request)
    {
        try {
            $err = [
                'device_id'           => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth                     = Auth::guard('api')->user();
            $wallet_amount            = CustomerWallet::getTotalAmount($Auth->id);
            if ($Auth->type == 'Customer') {
                $data = ['wallet_amount' => $wallet_amount];
                return response()->json(['status' => true, 'message' => $Auth->type . ' wallet detail', 'data' => $data]);
            }


            $earnings_amount          = CustomerWallet::where('customer_id', $Auth->id)->where('type', 'Credit')->sum('amount');
            $previous_withdrwa_amount = CustomerWallet::where('customer_id', $Auth->id)->where('is_withdrawal', '1')->where('withdrawal_status', 'TRANSFER_SUCCESS')->orwhere('withdrawal_status', 'SUCCESS')->sum('amount');

            $pending_withdrwa_amount  = CustomerWallet::where('customer_id', $Auth->id)
                ->where('is_withdrawal', '1')
                ->where('withdrawal_status', 'PENDING')
                ->orwhere('withdrawal_status', 'PAYOUT_PENDING')
                ->orwhere('withdrawal_status', 'SUCCESS')
                ->sum('amount');

            $get_wallet_arr          = [];
            $get_wallet_arr['title'] = 'Wallet Amount';
            $get_wallet_arr['value'] = CustomerWallet::getTotalAmount($Auth->id);
            $getarr[]                = $get_wallet_arr;


            $get_wallet_arr          = [];
            $get_wallet_arr['title'] = 'Previously Withdrawn';
            $get_wallet_arr['value'] = $previous_withdrwa_amount;
            $getarr[]                = $get_wallet_arr;


            $get_wallet_arr          = [];
            $get_wallet_arr['title'] = 'Pending Clearance';
            $get_wallet_arr['value'] = $pending_withdrwa_amount;
            $getarr[]                = $get_wallet_arr;

            $get_wallet_arr          = [];
            $get_wallet_arr['title'] = 'Total Earnings';
            $get_wallet_arr['value'] = $earnings_amount;
            $getarr[]                = $get_wallet_arr;

            $data = ['wallet_detail' => $getarr, 'wallet_amount' => $wallet_amount];
            return response()->json(['status' => true, 'message' => $Auth->type . ' wallet detail', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function check_call_wallet(Request $request)
    {
        try {
            // Validate the request
            $required_fields = [];
            $required_fields['device_id']                   = 'required';
            $required_fields['advisore_id']                 = 'required';
            $required_fields['advisore_availability_id']    = 'required';
            $required_fields['time']                        = 'required';
            $validation = Validator::make($request->all(), $required_fields);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }
            $Auth                  = Auth::guard('api')->user();
            $advisore_availability = CustomerAvailabilities::where(['id' => $request->advisore_availability_id, 'customer_id' => $request->advisore_id])->first();
            if ($advisore_availability) {
                $required_amount = ceil($request->time) * $advisore_availability->charges;  // Per Minute
                $wallet_amount   = CustomerWallet::getTotalAmount($Auth->id);
                if ($wallet_amount > 0) {
                    if ($wallet_amount >= $required_amount) {
                        return response()->json(['status' => true, 'message' => 'Order Place Successfully']);
                    } else {
                        return response()->json(['status' => false, 'message' => 'Insufficient Balance Please Add Money in your wallet'], 422);
                        die();
                    }
                } else {
                    return response()->json(['status' => false, 'message' => 'Insufficient Balance Please Add Money in your wallet']);
                    die();
                }
            } else {
                return response()->json(['status' => false, 'message' => 'availability not found'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }
}
