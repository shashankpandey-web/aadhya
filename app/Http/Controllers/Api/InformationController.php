<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Orders;
use App\Models\Page;
use App\Models\Notifications;
use App\Models\Customers;
use Illuminate\Support\Facades\Auth;

class InformationController extends Controller
{

    public function become_advisor(Request $request)
    {
        $get_data = Page::orderBy('id', 'desc')->where('id', 1)->first();
        return response()->json(['status' => true, 'message' => 'Become Advisor', 'data' => $get_data]);
        //return view('api.become-advisor',compact('get_data'));

    }

    public function application_process(Request $request)
    {
        $get_data = Page::orderBy('id', 'desc')->where('id', 2)->first();
        return response()->json(['status' => true, 'message' => 'Application Process', 'data' => $get_data]);
        //return view('api.application-process',compact('get_data'));
    }

    public function terms_conditions(Request $request)
    {   
        $data = '';
        $get_data = Page::orderBy('id', 'desc')->where('id', 3)->first();
        $data .= $get_data->description;
        $get_data = Page::orderBy('id', 'desc')->where('id', 4)->first();
        $data .= $get_data->description;
        $get_data = Page::orderBy('id', 'desc')->where('id', 5)->first();
        $data .= $get_data->description;
        $get_data = Page::orderBy('id', 'desc')->where('id', 6)->first();
        $data .= $get_data->description;
        $get_data = Page::orderBy('id', 'desc')->where('id', 7)->first();
        $data .= $get_data->description;
        return response()->json(['status' => true, 'message' => 'Terms Conditions', 'data' => $data]);
        //return view('api.terms-conditions',compact('get_data'));
    }

    public function notification_list(Request $request)
    {
        $Auth = Auth::guard('api')->user();
        $getNotifications = Notifications::select('id', 'customer_id', 'title', 'type', 'order_id')->where('customer_id', $Auth->id)->whereNull('is_delete')->orderBy('id', 'desc')->get();
        $getNotifications->transform(function ($notification) {
            
            $notification->advisor_id       = "";
            $notification->advisor_name     = "";
            $notification->advisor_image     = "";
            
            $notification->customer_id      = "";
            $notification->customer_name    = "";
            $notification->customer_image    = "";
            $getOrders = Orders::where('id',$notification->order_id)->first();
            if($getOrders){
                $getAdvisore = Customers::where('id',$getOrders['advisore_id'])->first();
                if($getAdvisore){
                    $notification->advisor_id    = $getAdvisore['id'];
                    $notification->advisor_image =  $getAdvisore['image'] ? url('uploads/image/' . $getAdvisore['image']) : url('uploads/placeholder/dummy_image.png');
                    $notification->advisor_name = $getAdvisore['full_name'] ? $getAdvisore['full_name'] : ''; 
                    $notification->is_online = ($getAdvisore['is_online']==1) ? true : false;         
                    $notification->is_busy = ($getAdvisore['is_busy']==1) ? true : false;         
                }

                $getCustomers = Customers::where('id',$getOrders['customer_id'])->first();
                if($getCustomers){
                    $notification->customer_id      = $getCustomers['id'];
                    $notification->customer_name    = $getCustomers['full_name'] ? $getCustomers['full_name'] : '';
                    $notification->customer_image    = $getCustomers['image'] ? url('uploads/image/' . $getCustomers['image']) : url('uploads/placeholder/dummy_image.png');;   
                    $notification->is_online = ($getCustomers['is_online']==1) ? true : false;   
                    $notification->is_busy = ($getCustomers['is_busy']==1) ? true : false;   
                }
            }

            $notification->order_id = $notification->order_id ? $notification->order_id : '';
            $notification->title    = $notification->title ? $notification->title : '';
            $notification->type     = $notification->type ? $notification->type : '';
            return $notification;
        });
        return response()->json(['status' => true, 'message' => 'Notification List', 'data' => $getNotifications]);
    }
}
