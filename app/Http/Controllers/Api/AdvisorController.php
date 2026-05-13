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
use App\Models\CustomerAvailabilities;
use App\Models\Orders;
use App\Models\OrderQuestions;
use App\Models\CustomerWallet;
use App\Models\AdvisorStripeAccount;
use App\Models\AdvisorBanks;
use App\Models\Chat;
use Carbon\Carbon;
use Stripe\Exception\ApiErrorException;
use App\Traits\CashfreeTraits;


class AdvisorController extends Controller
{

    use CashfreeTraits;
    public function advisor_home(Request $request)
    {

        try {
            // Validate the request
            $err = [
                'device_id'     => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            // $output=array();
            // $output['status']=false;

            // $validation = Validator::make($request->all(), [
            //     'customer_id'   => 'required',
            //     'password'      => 'required',
            //     'device_id'     => 'required',
            // ]);

            // if ($validation->fails()) {
            //     $output['message'] = $validation->errors()->first();
            //     return response()->json($output); die;
            // }

            $data = array();
            $Auth = Auth::guard('api')->user();
            
            /*$CustomerAvailabilities = CustomerAvailabilities::where(['customer_id' => $Auth->id])->get();
            $availability_arrs  = array();
            foreach ($CustomerAvailabilities as $key => $value) {
                $get_availability                = Availability::where(['id' => $value->availability_id])->first();
                $availability                    = array();
                $availability['id']              = $value->id;
                $availability['availability_id'] = $value->availability_id;
                $availability['title']           = $get_availability->title;
                $availability['sub_title']       = $get_availability->sub_title;
                $availability['image']           = $get_availability->image ? url('uploads/availability/' . $get_availability->image) : url('uploads/placeholder/dummy_image.png');
                $availability['charges']         = $value->charges ? $value->charges : 0;
       
                $availability['charges'] = $value->charges;
                $availability['status']  = $value->status;
                $availability_arrs[]     = $availability;
            }*/

            $availability_arrs  = array();
            $get_availability = Availability::where(['status' => 'Active'])->whereNull('is_delete')->orderBy('id', 'desc')->get();
            foreach ($get_availability as $key => $value1) {
                $availability                    = array();
                $availability['availability_id'] = $value1->id;
                $availability['title']           = isset($value1->title) ? $value1->title : '';
                $availability['sub_title']       = isset($value1->sub_title) ? $value1->sub_title : '';
                $availability['image']           = $value1->image ? url('uploads/availability/'.$value1->image) : url('uploads/placeholder/dummy_image.png');

                $availability['id']              = NULL;
                $availability['charges']         = 0;
                $availability['status']       = 'Deactive';

                $CustomerAvailabilities = CustomerAvailabilities::where(['availability_id' => $value1->id,'customer_id' => $Auth->id])->first();
                if ($CustomerAvailabilities) {
                    $availability['id']              = $CustomerAvailabilities->id;
                    $availability['charges']         = $CustomerAvailabilities->charges ? $CustomerAvailabilities->charges : 0;
                    $availability['status']       = isset($CustomerAvailabilities->status) ? $CustomerAvailabilities->status : '';
                }
                
                $availability_arrs[]        = $availability;
            }



            $data['my_availabilities'] = $availability_arrs;
            $data['total_orders']      = Orders::where(['advisore_id' => $Auth->id])->count();
            $data['total_clients']     = Orders::where(['advisore_id' => $Auth->id])->groupBy('customer_id')->count();
            //$data['total_earnings']    = '$' .Orders::where(['advisore_id' => $Auth->id])->sum('total_charges');
            $data['total_earnings']    = '$' .CustomerWallet::where('customer_id', $Auth->id)->where('type', 'Credit')->sum('amount');

            /*$get_clients = Orders::select('customer_id', DB::raw('count(*) as client_count'))
                            ->where(['advisore_id' => $Auth->id])
                            ->groupBy('customer_id')
                            ->get();
            $total_client = 0;               
            if( $get_clients ){
                foreach ($get_clients as $key => $value) {
                    $total_client+=$value->client_count;
                }
            }
            $data['get_clients']  = $total_client;*/

            /*$OrderArrs = [];
            if($my_orders){
                foreach ($my_orders as $key => $value) {
                    $OrderArr = [];
                    $OrderArr['id'] = $value->id;
                    $OrderArr['availability_type'] = $value->availability_type;
                    $OrderArr['charges'] = floatval($value->charges);
                    $OrderArr['charges_type'] = $value->charges_type;
                    $OrderArr['time'] = $value->time;
                    $OrderArr['total_charges'] = floatval($value->total_charges);
                    $OrderArr['created_at'] = $value->created_at;
                    $OrderArrs[] = $OrderArr;
                }
            }*/

            // $output['status']   = true;
            // $output['data']     = $data;
            // $output['message']  = 'Home Page';
            // return response()->json($output); 
            // die;

            $data['is_missed_call'] = false;

            $current_time = date('Y-m-d H:i:s');
            $next_minute = date('Y-m-d H:i:s',strtotime('+1 minute'));
            //$data['current_time'] = $current_time;
            //$data['next_minute'] = $next_minute;
            

            if ($Auth->is_missed_call && $Auth->is_missed_call_time) {
                $is_missed_call_time = strtotime($Auth->is_missed_call_time . ' +1 minute');
                //$data['is_missed_call_time'] = date('Y-m-d H:i:s',$is_missed_call_time);
                //$data['ds'] = date('Y-m-d H:i:s',$is_missed_call_time);
                if (strtotime($current_time)<=$is_missed_call_time) {
                    $data['is_missed_call'] = true;
                }
            }

            return response()->json(['status' => true, 'data' => $data, 'message' => 'Home Page']);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function change_advisor_rates(Request $request)
    {
        try {
            $err = [
                'device_id'             => 'required',
            ];

            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth = Auth::guard('api')->user();
            $availability_arrs = [];
            if ($request->my_availability_id) {
                foreach ($request->my_availability_id as $av_key => $av_value) {
                    $availability_arr                    = [];
                    $availability_arr['availability_id'] = $av_key;
                    $availability_arr['charges']         = $request->charges[$av_key];
                    if ($av_key == 3 || $av_key == 4) {
                        $availability_arr['charges_type'] = 'per_minute';
                    }
                    $availability_arrs[] = $availability_arr;

                    $get_advisore_availabilities = CustomerAvailabilities::where(['availability_id' => $av_key, 'customer_id' => $Auth->id])->first();
                    if ($get_advisore_availabilities) {
                        $get_advisore_availabilities->charges = $request->charges[$av_key];
                        $get_advisore_availabilities->status  = isset($request->status[$av_key]) && $request->status[$av_key] ? 'Active' : 'Deactive';
                        if ($av_key == 3 || $av_key == 4) {
                            $get_advisore_availabilities->charges_type = 'per_minute';
                        }
                        $get_advisore_availabilities->save();
                    }
                }
                return response()->json(['status' => true, 'message' => 'Update Advisor Rates']);
                die();
            } else {
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
                die();
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die();
        }
    }

    private function move_to_do_orders($Auth_id){

        $my_orders = Orders::where(['advisore_id' => $Auth_id,'order_availability_id' => '5','is_todo' => '0'])->get();
        foreach ($my_orders as $key => $value) {
            $order_id = $value->id;
             //echo "string ".$order_id; die;
            $customer_wallets = CustomerWallet::where(['order_id' => $order_id,'customer_id' => $Auth_id])->orderBy('id', 'desc')->first();
            if ($customer_wallets) {
                $diffInSeconds = $customer_wallets->created_at->diffInSeconds(Carbon::now());
                $chat_flag = false;
                if ($diffInSeconds<=300) {
                    $chat_flag = true;
                }
                $previousRecord = CustomerWallet::where('id', '<', $customer_wallets->id)->where(['order_id' => $order_id,'customer_id' => $Auth_id])->orderBy('id', 'desc')->first();
                if ($previousRecord) {
                    $previousdiffInSeconds = $previousRecord->created_at->diffInSeconds(Carbon::now());
                    if ($previousdiffInSeconds<=300) {
                        $chat_flag = true;
                    }
                }
                if ($chat_flag) {
                  
                }else{
                    $Orders = Orders::where(['id' => $order_id])->first();
                    $Orders->is_todo = 1;
                    $Orders->save();
                }
            }
        }
    }


    public function adviser_to_do_orders(Request $request)
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

            $Auth      = Auth::guard('api')->user();


            $this->move_to_do_orders($Auth->id);

            $limit     = config('apiconfig.records_per_page');
            $offset    = isset($request->offset) ? $request->offset * $limit : 0;


            $OrderArrs = [];
            $my_orders = Orders::where(['advisore_id' => $Auth->id]);
            $my_orders = $my_orders->select(
                'orders.*',
                'cc.full_name as customer_name',
                'cc.is_online as is_online',
                'cc.is_busy as is_busy',
                'cc.image as customer_image',
                'ca.availability_id',
                DB::raw('SUM(aa_orders.charges) as charges_sum')
            )->leftJoin('customers as cc', 'cc.id', '=', 'orders.customer_id')
                ->leftJoin('customer_availabilities as ca', 'ca.id', '=', 'orders.advisore_availability_id')
                ->leftJoin('availabilities as aa', 'aa.id', '=', 'ca.availability_id')
                ->whereIn('aa.id', [config('avaiblityconfig.1_day_delivery'), config('avaiblityconfig.1_hour_delivery')])->where('is_todo', 0);
            

            /*$my_orders = $my_orders->whereIn('orders.id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('orders')
                    ->groupBy('customer_id');
            });*/

            $my_orders = $my_orders->orderBy('orders.id', 'desc')->groupBy('orders.customer_id');


            $my_orders_count = $my_orders->count();
            $my_orders       = $my_orders->offset($offset)->take($limit)->get();

            if ($my_orders) {
                foreach ($my_orders as $key => $value) {
                    $OrderArr                      = [];
                    $OrderArr['id']                = $value->id;
                    $OrderArr['customer_id']       = $value->customer_id;
                    $OrderArr['advisore_id']       = $value->advisore_id;
                    $OrderArr['customer_name']     = isset($value->customer_name) ? $value->customer_name : '';
                    $OrderArr['is_online']             = ($value->is_online==1) ? true : false;
                    $OrderArr['is_busy']             = ($value->is_busy==1) ? true : false;
                    $OrderArr['customer_image']    = $value->customer_image ? url('uploads/image/' . $value->customer_image) : url('uploads/placeholder/dummy_image.png');;
                    $OrderArr['availability_type'] = isset($value->availability_type) ? $value->availability_type : '';
                    $OrderArr['charges']           = isset($value->charges) ? $value->charges : '';
                    $OrderArr['dob']           = isset($value->dob) ? $value->dob : '';
                    $OrderArr['gender']           = isset($value->gender) ? $value->gender : '';
                    $OrderArr['message']           = isset($value->message) ? $value->message : '';
                    $OrderArr['status_text']       = 'Paid';

                    $date                          = Carbon::parse($value->created_at);
                    $now                           = Carbon::now();
                    $diff                          = $now->diff($date);
                    $OrderArr['date']              = $date->format('M d, Y, g:i A');
                    if ($diff->d == 0) {
                        $OrderArr['date'] =  'Due by , ' . $date->addDay()->format('g:i A');
                    }
                    if ($diff->d == 1) {
                        $OrderArr['date'] =  'Due by Tomorrow, ' . $date->format('g:i A');
                    }

                    //$total_amount = CustomerWallet::where(['order_id' => $value->id,'customer_id' => $Auth->id])->sum('total_amount');
                    //$OrderArr['total_amount'] = get_price_format($total_amount);
                    $OrderArr['total_amount'] = isset($value->charges_sum) ? get_price_format($value->charges_sum) : '';
                    
                    $OrderArrs[]                   = $OrderArr;
                }
                return response()->json(['status' => true, 'message' => 'My Order List', 'data' => $OrderArrs, 'page_count' => ceil($my_orders_count / $limit)]);
                die;
            } else {
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die;
        }
    }

    public function advisor_my_order(Request $request)
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

            $Auth      = Auth::guard('api')->user();

            $this->move_to_do_orders($Auth->id);


            // $limit     = config('apiconfig.records_per_page');
            // $offset    = isset($request->offset) ? $request->offset * $limit : 0;

            $setLimit     = config('apiconfig.records_per_page');
            $offset       = 0;
            if ($request->offset) {
                $offset   = $request->offset-1;
            }
            $limit        = $offset * $setLimit;

            $OrderArrs = [];

            /*$my_orders = Orders::where(['advisore_id' => $Auth->id]);
            $my_orders = $my_orders->select(
                'orders.*',
                'cc.full_name as customer_name',
                'cc.image as customer_image',
                DB::raw('COUNT(advisore_availability_id) as services_count'),
                DB::raw('SUM(charges) as charges_sum')
            )->leftJoin('customers as cc', 'cc.id', '=', 'orders.customer_id')->groupby('customer_id');*/

            $my_orders = Orders::where(['advisore_id' => $Auth->id]);
            $my_orders = $my_orders->select(
                'orders.*',
                'cc.full_name as customer_name',
                'cc.is_online as is_online',
                'cc.is_busy as is_busy',
                'cc.image as customer_image',
                DB::raw('COUNT(advisore_availability_id) as services_count'),
                DB::raw('SUM(charges) as charges_sum'),
            )->where('orders.is_todo', 1)->leftJoin('customers as cc', 'cc.id', '=', 'orders.customer_id')->groupby('customer_id');

            //$my_orders_count = $my_orders->count();
            $my_orders_count = count($my_orders->get());
            $my_orders       = $my_orders->offset($limit)->take($setLimit)->get();
            if ($my_orders) {
                foreach ($my_orders as $key => $value) {
                    $OrderArr                    = [];
                    $OrderArr['id']              = $value->id;
                    $OrderArr['customer_id']       = $value->customer_id;
                    $OrderArr['advisore_id']       = $value->advisore_id;
                    $OrderArr['customer_name']     = isset($value->customer_name) ? $value->customer_name : '';
                    $OrderArr['is_online']             = ($value->is_online==1) ? true : false;
                    $OrderArr['is_busy']             = ($value->is_busy==1) ? true : false;
                    $OrderArr['customer_image']    = $value->customer_image ? url('uploads/image/' . $value->customer_image) : url('uploads/placeholder/dummy_image.png');;
                    $OrderArr['jobs_count']        = isset($value->services_count) ? $value->services_count : '';
                    $OrderArr['availability_type'] = isset($value->availability_type) ? $value->availability_type : '';
                    $OrderArr['total_charges']     = isset($value->charges_sum) ? get_price_format($value->charges_sum) : '';
                    $OrderArr['status_text']       = 'Paid';

                    //$total_amount = CustomerWallet::where(['order_id' => $value->id,'customer_id' => $Auth->id])->sum('total_amount');
                    //$OrderArr['total_amount'] = get_price_format($total_amount);
                    $OrderArr['total_amount'] = isset($value->charges_sum) ? get_price_format($value->charges_sum) : '';

                    $OrderArrs[]                   = $OrderArr;
                }
                return response()->json(['status' => true, 'message' => 'My Order List', 'data' => $OrderArrs, 'page_count' => ceil($my_orders_count / $setLimit), 'total_count' => $my_orders_count ]);
                die;
            } else {
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die;
        }
    }

    public function advisor_my_orde_detail(Request $request)
    {
        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'customer_id'   => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth        = Auth::guard('api')->user();
            $customer_id = $request->customer_id;

            $OrderArrs = [];
            $my_orders = Orders::where(['advisore_id' => $Auth->id, 'customer_id' => $customer_id]);
            $my_orders = $my_orders->select(
                'orders.*',
                'cc.full_name as customer_name',
                'cc.is_online as is_online',
                'cc.is_busy as is_busy',
                'cc.date_of_birth as date_of_birth',
                'cc.image as customer_image',
                DB::raw('COUNT(advisore_availability_id) as services_count'),
                DB::raw('SUM(charges) as charges_sum')
            )->leftJoin('customers as cc', 'cc.id', '=', 'orders.customer_id');
            $my_orders = $my_orders->groupby('customer_id')->first();

            // return $my_orders->toArray();
            if ($my_orders) {
                $OrderArr                           = [];
                $OrderArr['id']                     = $my_orders->id;
                $OrderArr['customer_id']            = $my_orders->customer_id;
                $OrderArr['advisore_id']            = $my_orders->advisore_id;
                $OrderArr['customer_date_of_birth'] = isset($my_orders->date_of_birth) ? date('M d, Y', strtotime($my_orders->date_of_birth)) : '';
                $OrderArr['customer_name']          = isset($my_orders->customer_name) ? $my_orders->customer_name : '';
                $OrderArr['is_online']             = ($my_orders->is_online==1) ? true : false;
                $OrderArr['is_busy']             = ($my_orders->is_busy==1) ? true : false;
                $OrderArr['customer_image']         = $my_orders->customer_image ? url('uploads/image/' . $my_orders->customer_image) : url('uploads/placeholder/dummy_image.png');;
                $OrderArr['jobs_count']             = isset($my_orders->services_count) ? $my_orders->services_count : '';
                $OrderArr['total_charges']          = isset($my_orders->charges_sum) ? $my_orders->charges_sum : '';
                $OrderArr['status_text']            = 'Paid';
                return response()->json(['status' => true, 'message' => 'My Order List', 'data' => $OrderArr]);
                die;
            } else {
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die;
        }
    }

    public function advisor_my_orde_detail_services(Request $request)
    {
        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'customer_id' => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $limit       = config('apiconfig.records_per_page');
            $offset      = isset($request->offset) ? $request->offset * $limit : 0;

            $setLimit     = config('apiconfig.records_per_page');
            $offset       = 0;
            if ($request->offset) {
                $offset   = $request->offset-1;
            }
            $limit        = $offset * $setLimit;

            $Auth        = Auth::guard('api')->user();
            $customer_id = $request->customer_id;



            $OrderArrs = [];
            $my_orders = Orders::where(['advisore_id' => $Auth->id, 'orders.customer_id' => $customer_id]);
            $my_orders = $my_orders->select('orders.*', 'ca.availability_id', 'aa.id as availability_id', 'aa.title as availability_title', 'aa.image as availability_image')->leftJoin('customer_availabilities as ca', 'ca.id', '=', 'orders.advisore_availability_id')
                ->leftJoin('availabilities as aa', 'aa.id', '=', 'ca.availability_id');
            if ($request->is_todo!='') {
                $my_orders = $my_orders->where('is_todo',$request->is_todo);
            }
            $my_orders_count =  count($my_orders->get());
            $my_orders = $my_orders->offset($limit)->take($setLimit)->orderBy('orders.id', 'desc')->get();

            if ($my_orders) {
                foreach ($my_orders as $key => $value) {
                    $OrderArr                       = [];
                    $OrderArr['id']                 = $value->id;
                    $OrderArr['customer_id']        = $value->customer_id;
                    $OrderArr['advisore_id']        = $value->advisore_id;
                    $OrderArr['availability_id']    = isset($value['availability_id']) ? $value['availability_id'] : '';
                    $OrderArr['availability_image'] = $value['availability_image'] ? url('uploads/availability/' . $value['availability_image']) : '';
                    $OrderArr['availability_name']  = $value['availability_title'] ? $value['availability_title'] : '';
                    $OrderArr['charges']            = isset($value->charges) ? $value->charges : '';
                    $OrderArr['charges_type']       = isset($value->charges_type) ? $value->charges_type : '';
                    $OrderArr['status_text']        = 'Paid';
                    $OrderArr['date']               = date('M d, Y', strtotime($value->created_at));


                    $total_amount = CustomerWallet::where(['order_id' => $value->id,'customer_id' => $Auth->id])->sum('total_amount');
                    $OrderArr['total_amount'] = get_price_format($total_amount);


                    $is_unread_message = Chat::where(['order_id' => $value->id,'to' => $Auth->id,'is_seen' =>'0'])->count();
                    $OrderArr['is_unread_message'] = $is_unread_message;

                    
                    $OrderArrs[]                    = $OrderArr;
                }
                return response()->json(['status' => true, 'message' => 'My Order List', 'data' => $OrderArrs, 'page_count' => ceil($my_orders_count / $setLimit), 'total_count' => $my_orders_count ]);
                die;
            } else {
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die;
        }
    }

     public function advisor_my_services_count(Request $request)
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
            $Auth        = Auth::guard('api')->user();
            $availability_id = array('1','2');

            $my_orders = Orders::where(['advisore_id' => $Auth->id]);
            foreach ($availability_id as $key => $value) {
                $my_orders = $my_orders->whereIn('orders.order_availability_id',$availability_id);
            }
            $my_orders = $my_orders->join('order_questions', 'orders.id', '=', 'order_questions.order_id')->where('order_questions.type', 'Answer')->distinct('orders.id')->count('orders.id');
            $my_orders_count = $my_orders;

             $availability_id = array('5');

            // $my_orders = Orders::where(['advisore_id' => $Auth->id]);
            // foreach ($availability_id as $key => $value) {
            //     $my_orders = $my_orders->whereIn('orders.order_availability_id',$availability_id);
            // }
            // $my_orders = $my_orders->join('order_questions', 'orders.id', '=', 'order_questions.order_id')->where('order_questions.type', 'Answer')->distinct('orders.id')->count('orders.id');

            $my_orders1 = Chat::whereRaw('chatID IN (SELECT MAX(chatID) FROM aa_chats_2 GROUP BY userid, `to`)')->where('to', $Auth->id)->select(DB::raw('COUNT(DISTINCT userid) as total'))->first()->total;
            $my_orders_count = $my_orders + $my_orders1;

            return response()->json(['status' => true, 'message' => 'My un-reply Order Count', 'data' => array(), 'count' => $my_orders_count]);
            die;
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die;
        }
    }

    public function advisor_my_order_chat(Request $request)
    {
        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'customer_id' => 'required',
                'order_id'    => 'required',
            ];


            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $get_order = Orders::where('id',$request->order_id)->first();

            if ($get_order) {

                $limit             = config('apiconfig.records_per_page');
                $offset            = isset($request->offset) ? $request->offset * $limit : 0;
                $OrderArrs = [];
                $getOrderQuestions      = OrderQuestions::where(['order_id' => $request->order_id]);
                $getOrderQuestionsCount = $getOrderQuestions->count();
                $getOrderQuestions      = $getOrderQuestions->offset($offset)->take($limit)->orderBy('id', 'asc')->get();
                if ($getOrderQuestions) {
                    foreach ($getOrderQuestions as $key => $value) {
                        $OrderArr           = [];
                        $OrderArr['id']     = $value->id;
                        $OrderArr['type']   = $value->type;
                        $OrderArr['detail'] = isset($value->detail) ? $value->detail : '';
                        $OrderArr['video']  = isset($value->video) ? get_image_upload_s3($value->video) : '';
                        $OrderArrs[]        = $OrderArr;
                    }

                    $Customers = Customers::where(['id' => $get_order->customer_id])->first();

                    return response()->json([
                        'status'     => true,
                        //'message'    => 'My Order Chat',
                        'message'    =>  ($get_order->message) ? $get_order->message :  '',
                        "has_more"   => $getOrderQuestionsCount > ($offset + $limit),
                        'full_name'       => ($get_order->full_name) ? $get_order->full_name :  '',
                        'customer_id'       => $Customers->id,
                        'is_online'       => ($Customers->is_online==1) ? true : false,
                        'is_busy'       => ($Customers->is_busy==1) ? true : false,
                        'dob'       => ($get_order->dob) ? $get_order->dob :  '',
                        'gender'       => ($get_order->gender) ? $get_order->gender :  '',
                        'data'       => $OrderArrs,
                        'page_count' => ceil($getOrderQuestionsCount / $limit)
                    ]);
                    die;
                } else {
                    return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
                }
            } else {
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die;
        }
    }


    public function add_order_chat(Request $request)
    {
        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'customer_id' => 'required',
                'order_id'    => 'required',
                'message'     => 'required',
            ];
            if (isset($request->video)) {
                //$err['video'] = 'max:15360';
                //$err['video'] = config('apiconfig.video_validation');
            }

            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $getOrderQuestions           = OrderQuestions::where(['order_id' => $request->order_id])->first();
            if ($getOrderQuestions) {
                $OrderQuestions           = new OrderQuestions();
                $OrderQuestions->order_id = $request->order_id;
                $OrderQuestions->type     = "Answer";
                $OrderQuestions->detail   = $request->message;
                if ($request->video) {
                    $imagePath = image_upload_s3($request->file('video'), "uploads/order_chat");
                    $OrderQuestions->video  = $imagePath;
                    Orders::where('id', $request->order_id)->update(array('is_todo' => 1));
                }
                
                $OrderQuestions->save();
                $data = [
                    'type'   => "Answer",
                    'detail' => $OrderQuestions->detail ? $OrderQuestions->detail : '',
                    'video'  => $OrderQuestions->video ? url('uploads/order_chat/' . $OrderQuestions->video) : '',
                ];
                return response()->json(['status' => true, 'message' => 'Message Send Successfull', 'data' => $data,]);
            } else {
                return response()->json(['status' => false, 'message' => 'Question Not Found']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die;
        }
    }


    public function advisor_earnings_chart(Request $request)
    {
        $err              = [];
        $err['device_id'] = 'required';
        $err['form_date'] = 'required';
        $err['to_date']   = 'required';
        $validation       = Validator::make($request->all(), $err);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
        }


        $form_date      = $request->form_date;
        $to_date        = $request->to_date;
        $total_earnings = 0;

        $Auth              = Auth::guard('api')->user();
        $getCustomerWallet = CustomerWallet::selectRaw('DATE(created_at) as date, SUM(amount) as amt')
            ->where('customer_id', $Auth->id)
            ->where('type', 'Credit')
            ->whereDate('created_at', '>=', $form_date)
            ->whereDate('created_at', '<=', $to_date);

        $total_earnings = $getCustomerWallet->sum('amount');

        $getCustomerWallet = $getCustomerWallet->groupBy('date')
            ->get();
        $getCustomerWallet->transform(function ($customerWallet) {
            $customerWallet->amt  = $customerWallet->amt ? $customerWallet->amt : '';
            $customerWallet->date = $customerWallet->date ? $customerWallet->date : '';
            return $customerWallet;
        });
        return response()->json([
            'status'   => true,
            'message'  => 'Earnings List',
            "data"     => $getCustomerWallet,
            "earnings" => $total_earnings,
        ]);
    }


    public function createOrUpdateStripeAccount(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'email' => 'required|email',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'postal_code' => 'required|string|max:10',
                'city' => 'required',
                'state' => 'required',
                'country' => 'required',
                'phone_number' => 'required|string|max:15',
                'tax_id' => 'nullable|string|max:20',
                'date_of_birth' => 'required',
                'vat_id' => 'required',


                // 'bank_name' => 'required',
                // 'account_holder_name' => 'required',
                // 'account_number' => 'required',
                // 'routing_number' => 'required',
                // 'branch_name' => 'required',
                'ssn_last_4' => 'required',
                // 'identity_document' => 'required',
                // 'business_type' => 'nullable|string|in:individual,company',
                // 'stripe_identity_company_document_id' => 'nullable|string|max:255',
            ]);

            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
            }


            $Auth = Auth::guard('api')->user();
            $AdvisorStripeAccount =  AdvisorStripeAccount::where('advisor_id', $Auth->id)->first();
            if (!$AdvisorStripeAccount) {
                $AdvisorStripeAccount =  new AdvisorStripeAccount();
            }
            extract($request->all());

            $Auth = Auth::guard('api')->user();

            $response             = $this->create_stripe_account($request, $AdvisorStripeAccount);
            $stripe_acc_id        = isset($response['stripe_acc_id']) ? $response['stripe_acc_id'] : '';
            $stripe_person_id     = isset($response['stripe_person_id']) ? $response['stripe_person_id'] : '';
            // $stripe_bank_id       = isset($response['stripe_bank_id']) ? $response['stripe_bank_id'] : '';
            $identy_proff_image   = isset($response['identy_proff_image']) ? $response['identy_proff_image'] : '';

            $AdvisorStripeAccount->advisor_id            = $Auth->id;
            $AdvisorStripeAccount->email                 = $email;
            $AdvisorStripeAccount->first_name            = $first_name;
            $AdvisorStripeAccount->last_name             = $last_name;
            $AdvisorStripeAccount->address               = $address;
            $AdvisorStripeAccount->postal_code           = $postal_code;
            $AdvisorStripeAccount->city                  = $city;
            $AdvisorStripeAccount->state                 = $state;
            $AdvisorStripeAccount->country               = $country;
            $AdvisorStripeAccount->phone_number          = $phone_number;
            $AdvisorStripeAccount->tax_id                = $tax_id;
            $AdvisorStripeAccount->date_of_birth         = $date_of_birth;
            $AdvisorStripeAccount->stripe_acc_id         = $stripe_acc_id;
            $AdvisorStripeAccount->stripe_person_id      = $stripe_person_id;
            // $AdvisorStripeAccount->stripe_bank_id        = $stripe_bank_id;
            // $AdvisorStripeAccount->bank_name             = $bank_name;
            // $AdvisorStripeAccount->account_holder_name   = $account_holder_name;
            // $AdvisorStripeAccount->account_number        = $account_number;
            // $AdvisorStripeAccount->routing_number        = $routing_number;
            // $AdvisorStripeAccount->branch_name           = $branch_name;
            $AdvisorStripeAccount->ssn_last_4            = $ssn_last_4;
            if ($identy_proff_image) {
                $AdvisorStripeAccount->identy_proff_file   = $identy_proff_image;
            }
            $AdvisorStripeAccount->save();
            return response()->json(['status' => true, 'message' => 'Stripe account processed successfully.']);
        } catch (ApiErrorException $e) {
            return response()->json([
                'status' => false,
                'error' => 'Stripe Error',
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function getStripeAccountDetail(Request $request)
    {
        $err              = [];
        $err['device_id'] = 'required';
        $validation       = Validator::make($request->all(), $err);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
        }

        $getDetail = [];
        $Auth = Auth::guard('api')->user();
        $AdvisorStripeAccount =  AdvisorStripeAccount::where('advisor_id', $Auth->id)->first();
        if ($AdvisorStripeAccount) {
            $getDetail['email']             = isset($AdvisorStripeAccount['email']) ? $AdvisorStripeAccount['email'] : '';
            $getDetail['first_name']        = isset($AdvisorStripeAccount['first_name']) ? $AdvisorStripeAccount['first_name'] : '';
            $getDetail['last_name']         = isset($AdvisorStripeAccount['last_name']) ? $AdvisorStripeAccount['last_name'] : '';
            $getDetail['address']           = isset($AdvisorStripeAccount['address']) ? $AdvisorStripeAccount['address'] : '';
            $getDetail['postal_code']       = isset($AdvisorStripeAccount['postal_code']) ? $AdvisorStripeAccount['postal_code'] : '';

            $getDetail['city']              = isset($AdvisorStripeAccount['city']) ? $AdvisorStripeAccount['city'] : '';
            $getDetail['city_name']         = '';
            if ($getDetail['city']) {
                $getCity = City::where('id', $getDetail['city'])->first();
                $getDetail['city_name'] = $getCity['name'];
            }
            $getDetail['state']             = isset($AdvisorStripeAccount['state']) ? $AdvisorStripeAccount['state'] : '';
            $getDetail['state_name']         = '';
            if ($getDetail['state']) {
                $getStates = States::where('id', $getDetail['state'])->first();
                $getDetail['state_name'] = $getStates['name'];
            }

            $getDetail['country']           = isset($AdvisorStripeAccount['country']) ? $AdvisorStripeAccount['country'] : '';
            $getDetail['country_name']         = '';
            if ($getDetail['country']) {
                $getCountries = Countries::where('id', $getDetail['country'])->first();
                if ($getCountries) {
                    $getDetail['country_name'] = $getCountries['name'];
                }
            }

            $getDetail['phone_number']      = isset($AdvisorStripeAccount['phone_number']) ? $AdvisorStripeAccount['phone_number'] : '';
            $getDetail['tax_id']            = isset($AdvisorStripeAccount['tax_id']) ? $AdvisorStripeAccount['tax_id'] : '';
            $getDetail['identy_proff_file'] = '';

            if ($AdvisorStripeAccount['identy_proff_file']) {
                $getDetail['identy_proff_file'] = url('uploads/advisor_identy', $AdvisorStripeAccount['identy_proff_file']);
            }

            // $getDetail['bank_name']            = isset($AdvisorStripeAccount['bank_name']) ? $AdvisorStripeAccount['bank_name'] : '';
            // $getDetail['account_holder_name']            = isset($AdvisorStripeAccount['account_holder_name']) ? $AdvisorStripeAccount['account_holder_name'] : '';
            // $getDetail['account_holder_type']            = isset($AdvisorStripeAccount['account_holder_type']) ? $AdvisorStripeAccount['account_holder_type'] : '';
            // $getDetail['account_number']            = isset($AdvisorStripeAccount['account_number']) ? $AdvisorStripeAccount['account_number'] : '';
            // $getDetail['routing_number']            = isset($AdvisorStripeAccount['routing_number']) ? $AdvisorStripeAccount['routing_number'] : '';
            // $getDetail['branch_name']            = isset($AdvisorStripeAccount['branch_name']) ? $AdvisorStripeAccount['branch_name'] : '';

            return response()->json([
                'status'   => true,
                'message'  => 'get Detail',
                "data"     => $getDetail,
            ]);
        }


        return response()->json(['status' => false, 'message' => 'Data Not Found']);
    }

    public function add_bank_details(Request $request)
    {
        try {
            $err = [
                'device_id'           => 'required',
                'bank_name'           => 'required',
                'account_holder_name' => 'required',
                'account_number'      => 'required',
                'routing_number'      => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth                              = Auth::guard('api')->user();

            $AdvisorStripeAccount = AdvisorStripeAccount::where('advisor_id', $Auth->id)->first();
            if (!$AdvisorStripeAccount) {
                return response()->json(['status' => false, 'message' => 'Please add Stripe Account Detail']);
            }
            if (!isset($AdvisorStripeAccount->stripe_acc_id)) {
                return response()->json(['status' => false, 'message' => 'Please add Stripe Account Detail']);
            }


            $responseData = $this->addStripeBankDetail($request, $err, $AdvisorStripeAccount->stripe_acc_id);
            if (!isset($responseData->id)) {
                return response()->json(['status' => false, 'message' => "Something went wrong"], 500);
            }
            extract($request->only(array_keys($err)));

            $AdvisorBanks = AdvisorBanks::where('customer_id', $Auth->id)->where('account_number', $account_number)->first();
            if (!$AdvisorBanks) {
                $AdvisorBanks                    = new AdvisorBanks();
            }
            $AdvisorBanks->customer_id           = $Auth->id;
            $AdvisorBanks->stripe_bank_id        = $responseData->id;
            $AdvisorBanks->bank_name             = $bank_name;
            $AdvisorBanks->account_number        = $account_number;
            $AdvisorBanks->routing_number        = $routing_number;
            $AdvisorBanks->account_holder_name   = $account_holder_name;
            $AdvisorBanks->is_delete             = null;
            $AdvisorBanks->json                  = \json_encode($responseData);
            $AdvisorBanks->status                = isset($responseData['status']) ? $responseData['status'] : '';
            $AdvisorBanks->save();
            return response()->json(['status' => true, 'message' => 'Bank Add Successfully']);
        } catch (ApiErrorException $e) {
            return response()->json([
                'status' => false,
                'error' => 'Stripe Error',
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function get_bank_detail_list(Request $request)
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


            $Auth            = Auth::guard('api')->user();

            $getBankDetails = [];
            $getAdvisorBanks = AdvisorBanks::where('customer_id', $Auth->id)->whereNull('is_delete')->get();
            if (!$getAdvisorBanks->isEmpty()) {

                foreach ($getAdvisorBanks as $key => $value) {
                    # code...
                    $get_arr                        = [];
                    $get_arr['id']                  = isset($value['id']) ? $value['id'] : '';
                    // $get_arr['stripe_bank_id']      = isset($value['stripe_bank_id']) ? $value['stripe_bank_id'] : '';
                    $get_arr['bank_name']           = isset($value['bank_name']) ? $value['bank_name'] : '';
                    $get_arr['routing_number']      = isset($value['routing_number']) ? $value['routing_number'] : '';
                    $get_arr['account_number']      = isset($value['account_number']) ? $value['account_number'] : '';
                    // $get_arr['branch_name']         = isset($value['branch_name']) ? $value['branch_name'] : '';
                    $get_arr['account_holder_name'] = isset($value['account_holder_name']) ? $value['account_holder_name'] : '';
                    // $get_arr['status']              = isset($value['status']) ? $value['status'] : '';
                    $getBankDetails[]               = $get_arr;
                }
                return response()->json(['status' => true, 'message' => 'Bank Details', 'data' => $getBankDetails]);
            }
            return response()->json(['status' => false, 'message' => 'No data Found', 'data' => []]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function get_bank_detail(Request $request)
    {
        try {
            $err = [
                'device_id'           => 'required',
                'bank_id'             => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth            = Auth::guard('api')->user();
            $get_arr         = [];

            $getAdvisorBanks = AdvisorBanks::where('customer_id', $Auth->id)->where('id', $request->bank_id)->whereNull('is_delete')->first();
            if ($getAdvisorBanks) {
                $get_arr['id']                  = isset($getAdvisorBanks['id'])      ? $getAdvisorBanks['id'] : '';
                // $get_arr['stripe_bank_id']      = isset($getAdvisorBanks['stripe_bank_id'])      ? $getAdvisorBanks['stripe_bank_id'] : '';
                $get_arr['bank_name']           = isset($getAdvisorBanks['bank_name'])           ? $getAdvisorBanks['bank_name'] : '';
                $get_arr['routing_number']      = isset($getAdvisorBanks['routing_number'])      ? $getAdvisorBanks['routing_number'] : '';
                // $get_arr['branch_name']         = isset($getAdvisorBanks['branch_name'])         ? $getAdvisorBanks['branch_name'] : '';
                $get_arr['account_holder_name'] = isset($getAdvisorBanks['account_holder_name']) ? $getAdvisorBanks['account_holder_name'] : '';
                $get_arr['status']              = isset($getAdvisorBanks['status'])              ? $getAdvisorBanks['status'] : '';

                return response()->json(['status' => true, 'message' => 'Bank Details', 'data' => $get_arr]);
            }
            return response()->json(['status' => false, 'message' => 'No data Found', 'data' => []]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }


    public function delete_bank_detail(Request $request)
    {
        try {
            $err = [
                'device_id'           => 'required',
                'bank_id'             => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth                 = Auth::guard('api')->user();
            $AdvisorStripeAccount = AdvisorStripeAccount::where('advisor_id', $Auth->id)->first();
            if (!$AdvisorStripeAccount) {
                return response()->json(['status' => false, 'message' => 'Stripe Account Not exists']);
            }
            if (!isset($AdvisorStripeAccount->stripe_acc_id)) {
                return response()->json(['status' => false, 'message' => 'Stripe Account Not exists']);
            }

            $getAdvisorBanks = AdvisorBanks::where('customer_id', $Auth->id)->where('id', $request->bank_id)->whereNull('is_delete')->first();
            if ($getAdvisorBanks) {
                // if ($getAdvisorBanks['stripe_bank_id']) {
                // $response = $this->deleteBankAccount($AdvisorStripeAccount->stripe_acc_id, $getAdvisorBanks['stripe_bank_id']);
                // if(isset($response->deleted)){
                $getAdvisorBanks->is_delete = 1;
                $getAdvisorBanks->save();
                return response()->json(['status' => true, 'message' => 'Delete Successfull']);
                // }
                // return response()->json(['status' => false, 'message' => 'something went wrong']);
                // }
                // return response()->json(['status' => false, 'message' => 'Data Not Found']);
            }
            return response()->json(['status' => false, 'message' => 'No data Found', 'data' => []]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'status' => false,
                'error' => 'Stripe Error',
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }
}
