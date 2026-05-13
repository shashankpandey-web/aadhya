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
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Banner;
use App\Models\Customers;
use App\Models\Customertarotseen;
use App\Models\Customerreport;
use App\Models\Customerreportimage;
use App\Models\Customersupport;
use App\Models\Customersupportimage;
use App\Models\Tarot;

class CustomerreportController extends Controller
{

    public function banner(Request $request)
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

            $data = array();
            $Auth = Auth::guard('api')->user();

            $Banner = Banner::where(['status' => 'Active'])->whereNull('is_delete')->orderBy('id', 'desc')->get();
            foreach ($Banner as $key => $value) {
                if ($value->image) {
                    $row = array();
                    $row['title']             = $value->title;
                    $row['image'] = url('uploads/banner/' . $value->image);
                    $data[] = $row;
                }
            }

            $message  = 'Banner List';
            return response()->json(['status' => true,'data' => $data, 'message' => $message]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function tarot(Request $request)
    {

        try {


            /*$Auth = Auth::guard('api')->user();

            $data = array(); 
            $data['name']  = $Auth->full_name;
            $data['email'] = $Auth->email;
            $data['phone_number']  = $Auth->phone_number;

            $data['subject']       = 'Your withdrawal has been successfully processed';
            $data['template']      = 'email.withdrawal_request_payout';
            if ($data['template']) {
                Mail::send($data['template'], $data, function ($message) use ($data) {
                    $message->to($data['email'], $data['name'])->subject($data['subject']);
                });
            }*/


            // Validate the request
            $err = [
                'device_id'   => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $data = array();
            $Auth = Auth::guard('api')->user();
            $Customers = Customers::where(['id' => $Auth->id])->first();

            $tarot_repeat_id = 1;
            if ($Customers->tarot_repeat_id) {
                $tarot_repeat_id = $Customers->tarot_repeat_id;
            }
            $Customertarotseen = Customertarotseen::where(['customer_id' => $Auth->id,'tarot_repeat_id' => $tarot_repeat_id])->pluck('tarot_id')->toArray();
            $LastCustomertarotseen = Customertarotseen::where(['customer_id' => $Auth->id,'tarot_repeat_id' => $tarot_repeat_id])->whereDate('created_at', Carbon::today())->first();
            $Tarot_count = Tarot::where(['status' => 'Active'])->whereNull('is_delete')->whereNotIn('id', $Customertarotseen)->count();

            if ($Tarot_count) {
            }else{
                $tarot_repeat_id = $Customers->tarot_repeat_id+1;
                $Customers->tarot_repeat_id     = $tarot_repeat_id;
                $Customers->save(); 
                $Customertarotseen = Customertarotseen::where(['customer_id' => $Auth->id,'customer_id' => $tarot_repeat_id])->pluck('tarot_id')->toArray();
                $LastCustomertarotseen = Customertarotseen::where(['customer_id' => $Auth->id,'tarot_repeat_id' => $tarot_repeat_id])->whereDate('created_at', Carbon::today())->first();
            }

            //$seen = true;
            $seen = false;
            if ($LastCustomertarotseen) {
                //echo $LastCustomertarotseen->id.' <= '.Carbon::today()->toDateString(); die;
                /*if ($LastCustomertarotseen && $LastCustomertarotseen->created_at->toDateString() <= Carbon::today()->toDateString()) {
                    $seen = false; 
                }*/
                $seen = true; 
            }


            $Tarot = Tarot::where(['status' => 'Active'])->whereNull('is_delete')->whereNotIn('id', $Customertarotseen)->orderByRaw('RAND()')->get();
            foreach ($Tarot as $key => $value) {
                $row = array();
                $row['tarot_id']             = $value->id;
                //$row['title']             = $value->title;
                //$row['message']             = $value->message;
                $row['image']             = url('uploads/placeholder/tarot.png');
                $data[] = $row;
            }

            $message  = 'Tarot List';
            return response()->json(['status' => true,'seen' => $seen,'data' => $data, 'message' => $message]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }


    public function tarotseen(Request $request){

        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'tarot_id'       => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Tarot = Tarot::where(['id' => $request->tarot_id])->whereNull('is_delete')->first();
            if ($Tarot) {
                $Auth = Auth::guard('api')->user();
                
                $Customers = Customers::where(['id' => $Auth->id])->first();
                $tarot_repeat_id = 1;
                if ($Customers->tarot_repeat_id) {
                    $tarot_repeat_id = $Customers->tarot_repeat_id;
                }

                $LastCustomertarotseen = Customertarotseen::where(['customer_id' => $Auth->id,'tarot_repeat_id' => $tarot_repeat_id])->first();
                if ($LastCustomertarotseen) {
                    //echo $LastCustomertarotseen->created_at->toDateString() .'<='. Carbon::today()->toDateString(); die;
                    /*if ($LastCustomertarotseen && $LastCustomertarotseen->created_at->toDateString() <= Carbon::today()->toDateString()) {
                        return response()->json(['status' => false,'seen' => false, 'message' => 'You have already seen the card.']);
                    }*/
                }

                
                $tarot_repeat_id = 1;
                if ($Customers->tarot_repeat_id) {
                    $tarot_repeat_id = $Customers->tarot_repeat_id;
                }else{
                    $Customers->tarot_repeat_id     = $tarot_repeat_id;
                    $Customers->save(); 
                }

            
                $Customertarotseen = new Customertarotseen();
                $Customertarotseen->customer_id = $Auth->id;
                $Customertarotseen->tarot_id     = $request->tarot_id;
                $Customertarotseen->device_id     = $request->device_id;
                $Customertarotseen->tarot_repeat_id     = $tarot_repeat_id;
                $Customertarotseen->save();              
                $message  = 'Thank you, tarot seen.';

                $data = array();
                $data['tarot_id']             = $Tarot->id;
                $data['title']             = $Tarot->title;
                $data['message']             = $Tarot->message;
                $data['image']             = url('uploads/placeholder/tarotseen.png');

                $Customers = Customers::where(['id' => $Auth->id])->first();
                $tarot_repeat_id = $Customers->tarot_repeat_id;
                $LastCustomertarotseen = Customertarotseen::where(['customer_id' => $Auth->id,'tarot_repeat_id' => $tarot_repeat_id])->first();

                $seen = true;
                /*if ($LastCustomertarotseen) {
                    if ($LastCustomertarotseen && $LastCustomertarotseen->created_at->toDateString() <= Carbon::today()->toDateString()) {
                        $seen = false; 
                    }
                }*/

                return response()->json(['status' => true, 'seen' => $seen,'data' => $data,'message' => $message]);
            } else {
                $message  = 'Something went wrong';
                return response()->json(['status' => false, 'message' => $message]);
            }

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function mycustomerreport(Request $request)
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

            $data = array();
            $Auth = Auth::guard('api')->user();

            $Customerreport = Customerreport::where(['customer_id' => $Auth->id])->whereNull('is_delete')->orderBy('id', 'desc')->get();
            foreach ($Customerreport as $key => $value) {
                $Customers = Customers::where(['id' => $value->to_customer_id])->whereNull('is_delete')->first();
                if ($Customers) {
                    $row = array();
                    $row['name']             = $Customers->full_name;
                    $row['is_online']    = ($Customers->is_online==1) ? true : false;
                    $row['is_busy']    = ($Customers->is_busy==1) ? true : false;
                    $row['profile']          = $Customers->image ? url('uploads/image/' . $Customers->image) : url('uploads/placeholder/dummy_image.png');
                    $row['reason'] = $value->reason;
                    $row['created_at'] = date('M d, Y', strtotime($value->created_at));

                    $images = array();
                    $Customerreportimage = Customerreportimage::where(['customer_report_id' => $value->id])->get();
                    foreach ($Customerreportimage as $key1 => $value1) {
                        if ($value1->image) {
                            $images[] = url('uploads/customerreport/' . $value1->image);
                        }
                    }

                    $row['images'] = $images;
                    $data[] = $row;
                }
            }

            $message  = 'My Customer Report';
            return response()->json(['status' => true,'data' => $data, 'message' => $message]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function customerreport(Request $request)
    {

        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'customer_id'       => 'required',
                'reason'     => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Customers = Customers::where(['id' => $request->customer_id])->whereNull('is_delete')->first();
            $Auth = Auth::guard('api')->user();

            if ($Customers) {
                $Customerreport = Customerreport::where(['customer_id' => $Auth->id,'to_customer_id' => $request->customer_id])->whereNull('is_delete')->first();
                
                if ($Customerreport) {
                    // code...
                }else{
                    $Customerreport = new Customerreport();
                    $Customerreport->customer_id = $Auth->id;
                    $Customerreport->to_customer_id     = $request->customer_id;
                    $Customerreport->device_id     = $request->device_id;
                    $Customerreport->reason     = $request->reason;
                    $Customerreport->save();

                    if ($request->hasFile('images')) {
                        foreach ($request->file('images') as $key => $img) {
                            $path = 'uploads/customerreport';
                            $imagePath = image_upload($img, $path);

                            $Customerreportimage = new Customerreportimage();
                            $Customerreportimage->customer_report_id     = $Customerreport->id;
                            $Customerreportimage->image     = $imagePath;
                            $Customerreportimage->save();
                        }
                    }

                    $support_site_email = get_setting_data('support_site_email', 'content'); 

                    $reason_text = $request->reason;
                    $data = array('subject' => "User Report Notification", 'support_site_email' => $support_site_email,'reason_text' => $reason_text,'Auth' => $Auth,'Customers' => $Customers, 'page' => 'email.api.customerreport');
                    
                    Mail::send($data['page'], $data, function ($message) use ($data) {
                        $message->to($data['support_site_email'])->subject($data['subject']);
                    });
                }
                $message  = 'Thank you, we’ll check shortly.';
            } else {
                $message  = 'Something went wrong';
            }

            return response()->json(['status' => true, 'message' => $message]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }


    public function mycustomesupport(Request $request)
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

            $data = array();
            $Auth = Auth::guard('api')->user();

            $Customersupport = Customersupport::where(['customer_id' => $Auth->id])->whereNull('is_delete')->orderBy('id', 'desc')->get();
            foreach ($Customersupport as $key => $value) {
                $row = array();
                $row['status'] = $value->status;
                $row['message'] = $value->message;
                $row['created_at'] = date('M d, Y', strtotime($value->created_at));
                $images = array();
                $Customersupportimage = Customersupportimage::where(['customer_support_id' => $value->id])->get();
                foreach ($Customersupportimage as $key1 => $value1) {
                    if ($value1->image) {
                        $images[] = url('uploads/customersupport/' . $value1->image);
                    }
                }
                $row['images'] = $images;
                $data[] = $row;
            }

            $message  = 'My Customer support ';
            return response()->json(['status' => true,'data' => $data, 'message' => $message]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function customesupport(Request $request)
    {

        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'message'     => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth = Auth::guard('api')->user();            
            $Customersupport = new Customersupport();
            $Customersupport->customer_id = $Auth->id;
            $Customersupport->device_id     = $request->device_id;
            $Customersupport->message     = $request->message;
            $Customersupport->save();

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $key => $img) {
                    $path = 'uploads/customersupport';
                    $imagePath = image_upload($img, $path);

                    $Customersupportimage = new Customersupportimage();
                    $Customersupportimage->customer_support_id     = $Customersupport->id;
                    $Customersupportimage->image     = $imagePath;
                    $Customersupportimage->save();
                }
            }

            $support_site_email = get_setting_data('support_site_email', 'content'); 

            $message_text = $request->message;
            $data = array('subject' => "User Support Notification", 'support_site_email' => $support_site_email,'message_text' => $message_text,'Auth' => $Auth,'page' => 'email.api.customersupport');

            $images = array();
            $Customersupportimage = Customersupportimage::where(['customer_support_id' => $Customersupport->id])->get();
            foreach ($Customersupportimage as $key1 => $value1) {
                if ($value1->image) {
                    $images[] = url('uploads/customersupport/' . $value1->image);
                }
            }
            $data['attachments'] = $images;
            
            Mail::send($data['page'], $data, function ($message) use ($data,$Auth) {
                $message->replyTo($Auth->email, $Auth->full_name);
                $message->to($data['support_site_email'])->subject($data['subject']);
                 //if (!empty($data['attachments']) && is_array($data['attachments'])) {
                    foreach ($data['attachments'] as $file) {
                        //if (file_exists($file)) {
                            $message->attach($file);
                        //}
                    }
                //}
            });
            $message  = 'Thank you, we’ll check shortly.';

            return response()->json(['status' => true, 'message' => $message]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }
}
