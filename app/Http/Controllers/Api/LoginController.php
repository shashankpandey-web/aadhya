<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Customers;
use App\Models\Customerdetail;
use App\Models\Logs;
use App\Models\Countries;
use App\Models\States;
use App\Models\City;
use App\Models\Categories;
use App\Models\CustomerCategories;
use App\Models\Availability;
use App\Models\CustomerAvailabilities;
use App\Models\Notifications;
use App\Models\Coupon;
use App\Models\CustomerWallet;
use App\Models\User;
use Mail;
use Image;
use Config;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{

    public function advisor_signup(Request $request)
    {

        try {

            if ($request->isMethod('post')) {
            } else {
                return response()->json(['status' => false, 'message' => 'Method Not Allowed. Please use POST for this API.'], 405);
            }

            $output = [];
            $error = [];
            $error['step']              = 'required|numeric';
            $error['device_id']         = 'required';
            //$error['user_type']         = 'required';

            if ($request->step == 1) {
                $error['full_name']         = 'required';
                $error['email']             = 'required|email|unique:customers,email';
                //$error['date_of_birth']     = 'required|date_format:Y-m-d';
                $error['paypal_id']           = 'required';
                //$error['country']           = 'required';
                //$error['state']             = 'required';
                //$error['city']              = 'required';
                //$error['location']          = 'required';
                //$error['phone_number']      = 'required|unique:customers,phone_number';
                $error['password']          = 'required';
                $error['confirm_password']  = 'required|same:password';
            } else {
                $error['customer_id']       = 'required|numeric';
                $error['password']          = 'required';
                if ($request->step == 2) {
                    $error['screen_name']   = 'required';
                    $error['service_name']  = 'required';
                    $error['image']       = 'required';
                } elseif ($request->step == 3) {
                    $error['categories.*'] = 'required';
                } elseif ($request->step == 4) {
                    $error['about_my_service'] = 'required';
                    $error['about_me']         = 'required';
                } elseif ($request->step == 5) {
                    $error['ordering_instructions'] = 'required';
                } elseif ($request->step == 6) {
                    //$error['my_video'] = 'required|'.config('apiconfig.video_validation');
                    $error['my_video'] = 'required';
                } elseif ($request->step == 7) {
                    $error['availabilities.*']    = 'required';
                }
            }

            $validation = Validator::make($request->all(), $error);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $message = 'Something went wrong';
            $message = 'Advisore Sign Up Successfully';

            if ($request->step == 1) {
                $customer                = new Customers();
                $customer->full_name     = $request->full_name;
                $customer->email         = $request->email;
                $customer->date_of_birth = $request->date_of_birth ? $request->date_of_birth : '';
                $customer->paypal_id     = $request->paypal_id;
                $customer->country       = $request->country ? $request->country : '';
                $customer->state         = $request->state ? $request->state : '';
                $customer->city          = $request->city ? $request->city : '';
                $customer->location      = $request->location ? $request->location : '';
                $customer->phone_number  = $request->phone_number ? $request->phone_number : '';
                $customer->password      = Hash::make($request->password);
                $customer->device_id     = $request->device_id;
                $customer->type          = 'Advisor';
                $customer->step          = $request->step;
                $output['step']          = $request->step + 1;
                $message                 = 'Personal Information saved Successfully';
            } else {

                $customer = Customers::where(['id' => $request->customer_id, 'type' => 'Advisor'])->first();
                if ($customer) {
                    if ($customer->id == $request->customer_id && $customer->password == $request->password) {

                        if ($customer->step != '') {
                            $customer->step = $request->step;
                        }
                        if ($request->step == 2) {
                            $customer->screen_name  = $request->screen_name;
                            $customer->service_name = $request->service_name;
                            $customer->image = $request->image;
                            if ($request->hasFile('image')) {
                                $path = 'uploads/image';
                                if ($customer && $customer->image) {
                                    $imagePath = image_upload($request->file('image'), $path, $customer->image);
                                } else {
                                    $imagePath = image_upload($request->file('image'), $path);
                                }
                                $customer->image = $imagePath;
                            }

                            $Customerdetail = Customerdetail::where(['customer_id' => $customer->id])->first();
                            if ($Customerdetail) {
                                // code...
                            }else{
                                $Customerdetail = new Customerdetail();
                            }
                            $Customerdetail->customer_id  = $customer->id;
                            $Customerdetail->gender  = $request->gender;
                            $Customerdetail->nationality  = $request->nationality;
                            $Customerdetail->psychic_advisor  = $request->psychic_advisor;
                            $Customerdetail->psychic_line  = $request->psychic_line;
                            $Customerdetail->social_media_name  = $request->social_media_name;
                            if ($request->hasFile('kyc_image')) {
                                $path = 'uploads/customerdetail';
                                $imagePath = image_upload($request->file('kyc_image'), $path);
                                $Customerdetail->kyc_image = $imagePath;
                            }
                            $Customerdetail->save();

                            $output['step']  = $request->step + 1;
                            $message = 'Public profile saved Successfully';
                        } elseif ($request->step == 3) {
                            if (is_array($request->categories) || is_object($request->categories)) {
                                //$customer->categories = json_encode($request->categories);   
                                foreach ($request->categories as $key => $value) {
                                    $CustomerCategories = CustomerCategories::where(['customer_id' => $request->customer_id, 'caterogy_id' => $value])->first();
                                    if ($CustomerCategories) {
                                        $CustomerCategories->delete();
                                    }
                                    $CustomerCategories = new CustomerCategories();
                                    $CustomerCategories->customer_id = $request->customer_id;
                                    $CustomerCategories->caterogy_id = $value;
                                    $CustomerCategories->save();
                                }
                            }
                            $output['step']  = $request->step + 1;
                            $message = 'Categories saved Successfully';
                        } elseif ($request->step == 4) {
                            $customer->about_my_service  = $request->about_my_service;
                            $customer->about_me = $request->about_me;
                            $output['step']  = $request->step + 1;
                            $message = 'About me saved Successfully';
                        } elseif ($request->step == 5) {
                            $customer->ordering_instructions = $request->ordering_instructions;
                            $output['step']  = $request->step + 1;
                            $message = 'Ordering instructions saved Successfully';
                        } elseif ($request->step == 6) {
                            if ($request->hasFile('my_video')) {
                                $path = 'uploads_profile';
                                if ($customer && $customer->my_video) {
                                    $imagePath = image_upload_s3($request->file('my_video'), $path);
                                } else {
                                    $imagePath = image_upload_s3($request->file('my_video'), $path);
                                }
                                $customer->my_video = $imagePath;
                            }

                            $output['step']  = $request->step + 1;
                            $message = 'Video Uploaded Successfully';
                        } elseif ($request->step == 7) {
                            if (is_array($request->availabilities) || is_object($request->availabilities)) {
                                //$customer->availabilities = json_encode($request->availabilities);   
                                $getAvailability = Availability::orderBy('id', 'desc')->get();
                                foreach ($getAvailability as $key => $value) {
                                    $CustomerAvailabilities = CustomerAvailabilities::where(['customer_id' => $request->customer_id, 'availability_id' => $value['id']])->first();
                                    if ($CustomerAvailabilities) {
                                        $CustomerAvailabilities->delete();
                                    }
                                    $CustomerAvailabilities                  = new CustomerAvailabilities();
                                    $CustomerAvailabilities->customer_id     = $request->customer_id;
                                    $CustomerAvailabilities->availability_id = $value['id'];
                                    $CustomerAvailabilities->status          = in_array($value['id'], $request->availabilities) ? 'Active' : 'Deactive';
                                    $CustomerAvailabilities->charges         = 5;

                                    if ($value['id'] == 3) {
                                        $CustomerAvailabilities->charges      = 10;
                                        $CustomerAvailabilities->charges_type = 'per_minute';
                                    }
                                    $CustomerAvailabilities->save();
                                }
                            }
                            $customer->step  = null;
                            $message = 'Thank you for applying for psychic advisor position. We are reviewing your application';


                            $userData              = User::where('role', 'ADMIN')->first();
                            if ($userData) {
                                $data['advisor_name']  = $customer->full_name;
                                $data['advisor_email'] = $customer->email;
                                $data['phone_number']  = $customer->phone_number;
                                if( $customer->date_of_birth != ''){
                                    $data['dob']       = date('Y-m-d', strtotime($customer->date_of_birth));
                                }                                
                                $data['register_date'] = date('Y-m-d', strtotime($customer->created_at));
                                $data['email']         = $userData['email'];
                                $data['subject']       = 'New Advisor Registration: Check Out the Details';
                                $data['template']      = 'email.new_advisor_mail';
                                if ($data['template']) {
                                    Mail::send($data['template'], $data, function ($message) use ($data) {
                                        $message->to($data['email'], $data['advisor_name'])
                                            ->subject($data['subject']);
                                    });
                                }
                            }
                        }
                    } else {
                        //return response()->json(['status' => true,'message' => 'Something went wrong']);
                        return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
                    }
                } else {
                    //return response()->json(['status' => true,'message' => 'Customer ID & Password Wrong']);
                    return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
                }
            }
            $customer->status = 'Inactive';
            $customer_save    = $customer->save();

            if ($customer_save && $customer->step == '') {
                // MAIL NOTIFICATION
                $data['subject'] = 'Complete Your Advisor Profile – Submit Your Test Reading Today';
                $data['name']    = $customer->full_name;
                $data['email']   = $customer->email;
                Mail::send('email.api.register_advisor_mail', $data, function ($message) use ($data) {
                    $message->to($data['email'], $data['name'])
                        ->subject($data['subject']);
                });
            }
            $output['customer_id']  = $customer->id;
            $output['user_type']    = $customer->type;
            $output['password']     = $customer->password;
            $output['full_name']    = $customer->full_name;
            $output['email']        = $customer->email;
            return response()->json(['status' => true, 'message' => $message, 'data' => $output]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function signup(Request $request)
    {
        try {
            if ($request->isMethod('post')) {
            } else {
                return response()->json(['status' => false, 'message' => 'Method Not Allowed. Please use POST for this API.'], 405);
            }
            // Validate the request
            $err = array(
                'full_name'        => 'required',
                'user_type'        => 'required',
                'email'            => 'required|email|unique:customers,email',
                //'date_of_birth'    => 'required|date_format:Y-m-d',
                //'country'          => 'required',
                //'country'          => 'required',
                //'state'            => 'required',
                //'city'             => 'required',
                //'location'         => 'required',
                //'phone_number'     => 'required|unique:customers,phone_number',
                'password'         => 'required|min:6',
                'confirm_password' => 'required|same:password|min:6',
                'device_id'        => 'required'
            );
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $customer = new Customers();
            $customer->full_name        = $request->full_name;
            $customer->email            = $request->email;
            $customer->date_of_birth    = $request->date_of_birth ? $request->date_of_birth : '';
            $customer->country          = $request->country ? $request->country : '' ;
            $customer->state            = $request->state  ? $request->state : '' ;
            $customer->city             = $request->city  ? $request->city : '' ;
            $customer->location         = $request->location  ? $request->location : '' ;
            $customer->phone_number     = $request->phone_number  ? $request->phone_number : '' ;
            $customer->password         = Hash::make($request->password);
            $customer->device_id        = $request->device_id;
            $customer->type             = $request->user_type;
            $customer->save();

            // First-order coupon — notify new customer if one is active
            $firstOrderCoupon = Coupon::where('is_first_order_offer', 1)
                ->where('status', 'Active')
                ->whereNull('is_delete')
                ->first();


               


            if ($firstOrderCoupon) {
                $couponTitle = "Welcome! Use code {$firstOrderCoupon->code} to get {$firstOrderCoupon->value}% off your first reading.";

                $Notification              = new Notifications();
                $Notification->customer_id = $customer->id;
                $Notification->title       = $couponTitle;
                $Notification->type        = 'first-order-offer';
                $Notification->save();
            }

            // Welcome email
            $data['subject'] = 'Welcome to Aadya Universe – Your Gateway to Spiritual Clarity';
            $data['name']    = $customer->full_name;
            $data['email']   = $customer->email;
            Mail::send('email.api.registration', $data, function ($message) use ($data) {
                $message->to($data['email'], $data['name'])
                    ->subject($data['subject']);
            });

            // First-order coupon email (separate, only if a coupon is active)
            if ($firstOrderCoupon) {
                $couponData                        = [];
                $couponData['name']                = $customer->full_name;
                $couponData['email']               = $customer->email;
                $couponData['coupon_code']         = $firstOrderCoupon->code;
                $couponData['discount_percentage'] = floatval($firstOrderCoupon->value);
                $couponData['subject']             = 'Your Exclusive ' . floatval($firstOrderCoupon->value) . '% Off – First Reading Offer!';
                Mail::send('email.first_order_offer', $couponData, function ($message) use ($couponData) {
                    $message->to($couponData['email'], $couponData['name'])
                        ->subject($couponData['subject']);
                });
            }

            $output = [];
            $output['customer_id']        = $customer->id;
            $output['password']           = $customer->password;
            $output['user_type']          = $customer->type;
            $output['full_name']          = $customer->full_name;
            $output['email']              = $customer->email;
            $output['phone_number']       = $customer->phone_number;
            $output['first_order_coupon'] = $firstOrderCoupon ? [
                'coupon_code' => $firstOrderCoupon->code,
                'type'        => $firstOrderCoupon->type,
                'value'       => floatval($firstOrderCoupon->value),
            ] : null;
            return response()->json(['status' => true, 'message' => 'Customer Sign Up Successfully', 'data' => $output]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function login(Request $request)
    {
        try {

            if ($request->isMethod('post')) {
            } else {
                return response()->json(['status' => false, 'message' => 'Method Not Allowed. Please use POST for this API.'], 405);
            }

            // Validate the request
            $err = [
                'email'     => 'required',
                'password'  => 'required',
                'device_id' => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            // Find customer by email
            $customer = Customers::where('email', $request->email)->whereNull('is_delete')->first();

            if (!$customer) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }

            if( $customer->type == 'Advisor' ){
                if( $customer->step != '' ){
                    return response()->json([
                            'status' => true, 
                            'step' => $customer->step, 
                            'message' => 'Please compelete your registration process', 
                            'is_varify' => false,
                            'customer_id' => $customer->id, 
                            'user_type' => $customer->type, 
                            'email' => $customer->email, 
                            'password'  => $customer->password,
                            'full_name'  => $customer->full_name,
                        ], 200);

 
                }else{
                    if ($customer->status == 'Inactive') {
                        return response()->json(
                            [
                                'status' => true, 
                                'message' => 'Your Account is inactive', 
                                'step' => $customer->step, 
                                'is_varify' => false
                            ], 200);
                    }else{
                        
                        if (Hash::check($request->password, $customer->password)) {
                            $credentials  = $request->only('email', 'password');
                            $credentials2 = ['email' => $customer->email, 'password' => $customer->password];
                            $token        = Auth::guard('api')->claims($credentials2)->attempt($credentials);
                            if (isset($request->fcm_token)) {
                                Customers::where(['fcm_token' => $request->fcm_token])->update(['fcm_token' => NULL]);
                                $customer->fcm_token = $request->fcm_token;
                            }
                            $customer->jwt_token = $token;
                            $customer->save();
                            return response()->json([
                                'status'        => true,
                                'access_token'  => $token,
                                'password'      => $customer->password,
                                'token_type'    => 'bearer',
                                'message'       => 'Logged in successfully', 
                                'step'          => $customer->step , 
                                'is_varify'     => true
                            ],200);
                        }
                    }
                }
            }

            if ($customer->status == 'Inactive') {
                return response()->json(['status' => false, 'message' => 'Your Account is inactive '], 401);
            }

            if (Hash::check($request->password, $customer->password)) {
                $credentials  = $request->only('email', 'password');
                $credentials2 = ['email' => $customer->email, 'password' => $customer->password];
                $token        = Auth::guard('api')->claims($credentials2)->attempt($credentials);
                if (isset($request->fcm_token)) {
                    Customers::where(['fcm_token' => $request->fcm_token])->update(['fcm_token' => NULL]);
                    $customer->fcm_token = $request->fcm_token;
                }
                $customer->jwt_token = $token;
                $customer->save();
                return response()->json([
                    'status'        => true,
                    'access_token'  => $token,
                    'password'      => $customer->password,
                    'token_type'    => 'bearer',
                    'message'       => 'Login'
                ]);
            } else {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function refreshToken(Request $request)
    {
        try {

            if ($request->isMethod('post')) {
            } else {
                return response()->json(['status' => false, 'message' => 'Method Not Allowed. Please use POST for this API.'], 405);
            }

            // Validate the request
            $err = [
                'email'     => 'required',
                'password'  => 'required',
                'device_id' => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            // Find customer by email
            $customer = Customers::where('email', $request->email)->where('password', $request->password)->where('jwt_token', JWTAuth::getToken())->where('status', 'Active')->whereNull('is_delete')->first();
            if (!$customer) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }

            $credentials = ['email' => $customer->email, 'password' => $customer->password];
            $token        = Auth::guard('api')->claims($credentials)->login($customer);
            $customer->jwt_token = $token;
            $customer->save();
            return response()->json([
                'status'        => true,
                'access_token'  => $token,
                'password'      => $customer->password,
                'token_type'    => 'bearer',
                'message'       => 'Login  '
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function social_login(Request $request)
    {
        try {
            if ($request->isMethod('post')) {
            } else {
                return response()->json(['status' => false, 'message' => 'Method Not Allowed. Please use POST for this API.'], 405);
            }

            // Validate the request
            $err = [
                'device_id'             => 'required',
                'email'                 => 'required',
                'full_name'             => 'required',
                'social_media_type'     => 'required',
                'social_media_profile'  => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $data = [];
            $customer = Customers::where(['email' => $request->email])->first();
            if (!$customer) {
                $random_password     = random_int(100000, 999999);
                $customer            = new Customers();
                $customer->full_name = $request->full_name;
                $customer->email     = $request->email;
                $customer->password  = Hash::make($random_password);
                $customer->device_id = $request->device_id;
                $customer->type      = $request->user_type;
                if ($customer->user_type == 'Advisor') {
                    $customer->step  = 1;
                }
                $customer->social_media_type = $request->social_media_type;
                $customer->social_media_profile = $request->social_media_profile;
                if (isset($request->fcm_token)) {
                    Customers::where(['fcm_token' => $request->fcm_token])->update(['fcm_token' => NULL]);
                    $customer->fcm_token = $request->fcm_token;
                }
                $customer->save();

                $name   = $request->full_name;
                $email  = $request->email;
                $data   = array('subject' => "Genrated Password", 'email' => $email, 'name' => $name, 'password' => $random_password, 'page' => 'email.api.socialpassword');
                Mail::send($data['page'], $data, function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['subject']);
                });
            }
            if (isset($request->fcm_token)) {
                Customers::where(['fcm_token' => $request->fcm_token])->update(['fcm_token' => NULL]);
                $customer->fcm_token = $request->fcm_token;
                $customer->save();
            }
            if ($request->user_type == 'Advisor') {
                $data['step']         = $customer->step;
            }

            $data['customer_id'] = $customer->id;
            $data['user_type']   = $customer->type;
            $data['password']    = $customer->password;
            $data['full_name']   = $customer->full_name;
            $data['email']       = $customer->email;

            $credentials2 = ['email' => $customer->email, 'password' => $customer->password];

            $token               = Auth::guard('api')->claims($credentials2)->login($customer);
            $data['accessToken'] = $token;
            $data['password']    = $customer->password;

            return response()->json(['status' => true, 'message' => 'Login with ' . $request->social_media_type, 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function logout(Request $request)
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
            $customer = Customers::where(['id' => $Auth->id])->first();
            if ($customer) {
                Customers::where(['fcm_token' => $customer->fcm_token])->update(['fcm_token' => NULL]);
                $customer->fcm_token = NULL;
                //$customer->jwt_token = NULL;
                $customer->save();
            }
            return response()->json(['status' => true, 'message' => 'logout successfully']);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    // Get authenticated customer
    public function profile(Request $request)
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

            $data = [];
            $Auth = Auth::guard('api')->user();

            $customer = Customers::where(['id' => $Auth->id, 'status' => 'Active'])->whereNull('is_delete')->first();
            $data = array();
            $data['customer_id']    = $customer['id'];
            $data['user_type']      = $customer['type'];
            $data['full_name']      = $customer['full_name'];
            $data['email']          = $customer['email'];
            $data['date_of_birth']  = $customer['date_of_birth'];

            $country_arr = [];
            $get_country = Countries::where(['id' => $customer['country']])->first();
            if ($get_country) {
                $country_arr['id']   = $get_country['id'];
                $country_arr['name'] = $get_country['name'];
                $data['country']     = $country_arr;
            }

            $state_arr = [];
            $get_state = States::where(['id' => $customer['state']])->first();
            if ($get_state) {
                $state_arr['state_id']   = $get_state['id'];
                $state_arr['state_name'] = $get_state['name'];
                $data['state']           = $state_arr;
            }

            $city_arr = [];
            $get_city = City::where(['id' => $customer['city']])->first();
            if ($get_city) {
                $city_arr['city_id']   = $get_city['id'];
                $city_arr['city_name'] = $get_city['name'];
                $data['city']          = $city_arr;
            }


            $data['location']      = $customer['location'];
            $data['phone_number']  = $customer['phone_number'];
            $data['image']         = $customer['image'] ? url('uploads/image/' . $customer['image']) : url('uploads/placeholder/dummy_image.png');
            $data['user_type']     = $customer['type'];
            $data['wallet_amount'] = $data['withdrawal_amount'] = CustomerWallet::getTotalAmount($Auth->id);
            $data['category_id']   = [];
            if ($customer['type'] == 'Advisor') {

                $data['paypal_id']          = $customer['paypal_id'] ? $customer['paypal_id'] : '' ;

                $customer_category_arr = [];
                $get_customer_categories = CustomerCategories::where(['customer_id' => $customer['id']])->get();
                if ($get_customer_categories) {
                    foreach ($get_customer_categories as $key => $value) {
                        $get_customer_category = Categories::where(['id' => $value->caterogy_id])->first();
                        if ($get_customer_category) {
                            $data['category_id'][]   = $get_customer_category->id;
                            $customer_category_arr[] = $get_customer_category->title;
                        }
                    }
                }

                $data['category']              = implode(', ', $customer_category_arr);
                $data['screen_name']           = $customer['screen_name'] ? $customer['screen_name'] : 'S';
                $data['service_name']          = $customer['service_name'];
                $data['about_my_service']      = $customer['about_my_service'];
                $data['about_me']              = $customer['about_me'];
                $data['ordering_instructions'] = $customer['ordering_instructions'];
                $data['step']                  = $customer->step;
                $data['my_video']              = $customer['my_video'] ? get_image_upload_s3($customer['my_video']) : '';
            }
            return response()->json(['status' => true, 'message' => 'Profile', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function profileupdate(Request $request)
    {
        try {
            // Validate the request
            $err = [];
            $err['device_id']         = 'required';
            //$err['customer_id']       = 'required';
            //$err['password']          = 'required';
            $err['full_name']         = 'required';
            //$err['date_of_birth']     = 'required|date_format:Y-m-d';
            // $err['country']           = 'required';
            // $err['state']             = 'required';
            // $err['city']              = 'required';
            // $err['location']          = 'required';
            // $err['phone_number']      = 'required';


            $Auth = Auth::guard('api')->user();

            $customer = Customers::where(['id' => $Auth->id, 'status' => 'Active'])->whereNull('is_delete')->first();
            if ($customer->type == 'Advisor') {
                $err['paypal_id']             = 'required';
                $err['categories.*']          = 'required';
                $err['screen_name']           = 'required';
                $err['service_name']          = 'required';
                $err['about_my_service']      = 'required';
                $err['about_me']              = 'required';
                $err['ordering_instructions'] = 'required';
                //$err['my_video']              = config('apiconfig.video_validation');
                $err['my_video']              = 'required';
            }

            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            /*$Logs                           = new Logs();
            $Logs->title              = 'profileupdate api request';
            $Logs->data              = json_encode($request->all());
            $Logs->save();*/

            $data = [];
            if ($customer) {

                //$data['get_customer']  = $customer;

                $SaveCustomers = Customers::where(['id' => $Auth->id])->first();
                $SaveCustomers->id             = $customer['id'];
                $SaveCustomers->full_name      = $request->full_name;
                $SaveCustomers->date_of_birth  = $request->date_of_birth;
                $SaveCustomers->country        = $request->country;
                $SaveCustomers->state          = $request->state;
                $SaveCustomers->city           = $request->city;
                $SaveCustomers->location       = $request->location;
                $SaveCustomers->phone_number   = $request->phone_number;

                if ($request->hasFile('image')) {
                    $path = 'uploads/image';
                    $imagePath = image_upload($request->file('image'), $path);
                    $SaveCustomers->image = $imagePath;
                }

                if ($customer->type == 'Advisor') {

                    $SaveCustomers->paypal_id           = $request->paypal_id;
                    
                    /*if (is_array($request->availabilities) || is_object($request->availabilities)) {
                        $availability_arr = [];
                        $get_availabilities = Availability::get();
                        if($get_availabilities){
                            foreach ($get_availabilities as $all_key => $all_value) {
                                if( !in_array($all_value->id, $request->availabilities )){
                                    CustomerAvailabilities::where(['customer_id' => $Auth->id, 'availability_id' => $all_value->id ])->delete();
                                }
                            }
                        }

                        foreach ($request->availabilities as $key => $value) {
                            $GetCustomerAvailabilities = CustomerAvailabilities::where(['customer_id' => $Auth->id, 'availability_id' => $value ])->first();
                            if($GetCustomerAvailabilities){
                                $CustomerAvailabilities = CustomerAvailabilities::find($GetCustomerAvailabilities->id);
                            }else{
                                $CustomerAvailabilities = new CustomerAvailabilities();
                                if( $value == 3){ 
                                    $CustomerAvailabilities->charges = 10;
                                    $CustomerAvailabilities->charges_type = 'per_minute';
                                }
                            }
                            $CustomerAvailabilities->customer_id = $Auth->id;
                            $CustomerAvailabilities->availability_id = $value;
                            $CustomerAvailabilities->save();
                        }
                    }*/

                    if (is_array($request->categories) || is_object($request->categories)) {
                        $availability_arr = [];
                        $getCategories = Categories::get();
                        if ($getCategories) {
                            foreach ($getCategories as $all_key => $all_value) {
                                if (!in_array($all_value->id, $request->categories)) {
                                    CustomerCategories::where(['customer_id' => $Auth->id, 'caterogy_id' => $all_value->id])->delete();
                                }
                            }
                        }

                        foreach ($request->categories as $key => $value) {
                            $GetCustomerCategories = CustomerCategories::where(['customer_id' => $Auth->id, 'caterogy_id' => $value])->first();
                            if ($GetCustomerCategories) {
                                $CustomerCategories = CustomerCategories::find($GetCustomerCategories->id);
                            } else {
                                $CustomerCategories = new CustomerCategories();
                            }
                            $CustomerCategories->customer_id = $Auth->id;
                            $CustomerCategories->caterogy_id = $value;
                            $CustomerCategories->save();
                        }
                    }
                    $SaveCustomers->screen_name           = $request->screen_name;
                    $SaveCustomers->service_name          = $request->service_name;
                    $SaveCustomers->about_my_service      = $request->about_my_service;
                    $SaveCustomers->about_me              = $request->about_me;
                    $SaveCustomers->ordering_instructions = $request->ordering_instructions;
                    if ($request->hasFile('my_video')) {
                        $path = 'uploads_profile';
                        if ($customer->my_video) {
                            $imagePath = image_upload_s3($request->file('my_video'), $path);
                        } else {
                            $imagePath = image_upload_s3($request->file('my_video'), $path);
                        }
                        $SaveCustomers->my_video = $imagePath;
                    }
                }
                $SaveCustomers->save();

                $UpdatedCustomer     = Customers::where(['id' => $Auth->id])->first();
                $data['customer_id'] = $UpdatedCustomer->id;
                $data['user_type']   = $UpdatedCustomer->type;
                $data['full_name']   = $UpdatedCustomer->full_name;
                $data['email']       = $UpdatedCustomer->email;
                if ($UpdatedCustomer->image) {
                    $data['image']        = url('uploads/customers/' . $UpdatedCustomer->image);
                } else {
                    $data['image']        = url('assets/img/user.png');
                }

                $customer_category_arr = [];
                $get_customer_categories = CustomerCategories::where(['customer_id' => $customer->id])->get();
                if ($get_customer_categories) {
                    foreach ($get_customer_categories as $key => $value) {
                        $get_customer_category = Categories::where(['id' => $value->caterogy_id])->first();
                        if ($get_customer_category) {
                            $customer_category_arr[] = $get_customer_category->title;
                        }
                    }
                }

                $data['category']              = implode(', ', $customer_category_arr);
                $data['screen_name']           = $UpdatedCustomer['screen_name'] ? $UpdatedCustomer['screen_name'] : 'S';
                $data['service_name']          = $UpdatedCustomer['service_name'];
                $data['about_my_service']      = $UpdatedCustomer['about_my_service'];
                $data['about_me']              = $UpdatedCustomer['about_me'];
                $data['ordering_instructions'] = $UpdatedCustomer['ordering_instructions'];
                $data['my_video']              = $UpdatedCustomer['my_video'] ? get_image_upload_s3($UpdatedCustomer['my_video']) : '';
            }

            return response()->json(['status' => true, 'message' => 'Profile Update', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }


    public function is_missed_call(Request $request)
    {
        try {

            /*$Logs                           = new Logs();
            $Logs->title              = 'is_missed_call api request';
            $Logs->data              = json_encode($request->all());
            $Logs->save();*/
            
            $err = [];
            $err['advisor_id']         = 'required';
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth = Auth::guard('api')->user();
            //$customer = Customers::where(['id' => $Auth->id, 'status' => 'Active'])->whereNull('is_delete')->first();
            $SaveCustomers = Customers::where(['id' => $request->advisor_id])->first();
            if ($SaveCustomers) {
                $SaveCustomers->is_missed_call = (isset($request->is_missed_call) && $request->is_missed_call == 1) ? 1 : 0;
                $SaveCustomers->is_missed_call_time = date('Y-m-d H:i:s');
                $SaveCustomers->save();

                $SaveCustomers = Customers::where(['id' => 11])->first();
                $SaveCustomers->is_missed_call = (isset($request->is_missed_call) && $request->is_missed_call == 1) ? 1 : 0;
                $SaveCustomers->is_missed_call_time = date('Y-m-d H:i:s');
                $SaveCustomers->save();

                return response()->json(['status' => true, 'message' => 'Is missed call Update'.$Auth->id]);
            }else{
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 401);
            }

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function changepassword(Request $request)
    {
        try {
            // Validate the request
            $err = [
                'device_id'        => 'required',
                'current_password' => 'required',
                'new_password'     => 'required|min:6',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth = Auth::guard('api')->user();
            $customer = Customers::where(['id' => $Auth->id, 'status' => 'Active'])->whereNull('is_delete')->first();
            if (Hash::check($request->current_password, $customer->password)) {
                $data = array();
                $output['status'] = true;
                $data['password']   = Hash::make($request->new_password);
                $new_g_passwword    = $data['password'];
                Customers::where('id', $customer->id)->update($data);
                if ($customer->email) {
                    $SaveNotification = new Notifications();
                    $SaveNotification->customer_id = $customer->id;
                    $SaveNotification->title = 'Your password has been changed successfully';
                    $SaveNotification->type = 'password-change';
                    $SaveNotification->save();

                    $full_name  = $customer->full_name;
                    $email      = $customer->email;
                    $data = array('subject' => "Change Password", 'email' => $email, 'name' => $full_name, 'page' => 'email.api.changepassword');
                    Mail::send($data['page'], $data, function ($message) use ($data) {
                        $message->to($data['email'])->subject($data['subject']);
                    });
                }

                $credentials2 = ['email' => $customer->email, 'password' => $new_g_passwword];
                $credentials  = ['email' => $customer->email, 'password' => $request->new_password];
                $token        = Auth::guard('api')->claims($credentials2)->attempt($credentials);
                return response()->json(['status' => true, 'message' => 'Changed Password', 'token' => $token, 'password' => $new_g_passwword]);
            } else {
                return response()->json(['status' => false, 'message' => 'Your Current password does not match.'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function Deletemyaccount(Request $request)
    {
        try {
            $output = array();
            $output['status'] = false;

            $validation = Validator::make($request->all(), [
                'device_id'         => 'required',
            ]);

            if ($validation->fails()) {
                $output['message'] = $validation->errors()->first();
                return response()->json($output);
                die;
            }
            $Auth = Auth::guard('api')->user();
            $customer = Customers::where(['id' => $Auth->id, 'status' => 'Active'])->whereNull('is_delete')->first();
            if ($customer) {
                $data = array();
                $data['is_delete'] = 1;
                $data['email'] = "delete_" . date('d-m-Y') . "_" . $customer->email;
                $data['phone_number'] = "delete_" . date('d-m-Y') . "_" . $customer->phone_number;
                Customers::where('id', $customer['id'])->update($data);

                // MAIL NOTIFICATION
                if ($customer->email) {
                    $name          = $customer->full_name;
                    $email         = $customer->email;
                    $data = array('subject' => "Your Delete Account Confirmation", 'email' => $email, 'name' => $name, 'page' => 'email.api.deletemyaccount');
                    Mail::send($data['page'], $data, function ($message) use ($data) {
                        $message->to($data['email'])->subject($data['subject']);
                    });
                }
                $output['status']  = true;
                $output['message'] = 'Your Account Deleted successfully';
            }           
            return response()->json($output);die;
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function forgotpasword(Request $request)
    {
        try {
            if ($request->isMethod('post')) {
            } else {
                return response()->json(['status' => false, 'message' => 'Method Not Allowed. Please use POST for this API.'], 405);
            }

            // Validate the request
            $err = [
                'device_id' => 'required',
                'email'     => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $data = array();
            $customer = Customers::where(['email' => $request->email, 'status' => 'Active'])->whereNull('is_delete')->first();
            if ($customer) {
                $otp = random_int(100000, 999999);
                $otp = 1234;
                $customer->otp   = $otp;
                $customer->save();
                $name   = $customer->full_name;
                $email  = $customer->email;
                $data = array('subject' => "Reset Password OTP", 'email' => $email, 'name' => $name, 'otp' => $otp, 'page' => 'email.api.forgotpassword');
                Mail::send($data['page'], $data, function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['subject']);
                });
                return response()->json(['status' => true, 'message' => 'Mail Send Successfully. Please check OTP on the your email.']);
            } else {
                return response()->json(['status' => false, 'message' => 'This email address is not exits, Unauthorized'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function verifyotp(Request $request)
    {
        try {
            if ($request->isMethod('post')) {
            } else {
                return response()->json(['status' => false, 'message' => 'Method Not Allowed. Please use POST for this API.'], 405);
            }
            // Validate the request
            $err = [
                'device_id' => 'required',
                'email'     => 'required',
                'otp'       => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $customer = Customers::where(['email' => $request->email, 'status' => 'Active'])->whereNull('is_delete')->first();
            if ($customer) {
                //$customer = Customers::where(['email' => $request->email,'otp' => $request->otp,'status' => 'Active'])->whereNull('is_delete')->first();
                if ($customer->otp == $request->otp) {
                    return response()->json(['status' => true, 'message' => 'OTP verifed successfully']);
                } else {
                    return response()->json(['status' => false, 'message' => 'OTP not verify']);
                }
            } else {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function resetpasword(Request $request)
    {

        try {

            if ($request->isMethod('post')) {
            } else {
                return response()->json(['status' => false, 'message' => 'Method Not Allowed. Please use POST for this API.'], 405);
            }

            // Validate the request
            $err = [
                'device_id'    => 'required',
                'email'        => 'required',
                'otp'          => 'required',
                'new_password' => 'required|min:6',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $customer = Customers::where(['email' => $request->email, 'status' => 'Active'])->whereNull('is_delete')->first();
            if ($customer) {
                if ($customer->otp == $request->otp) {
                    $data = array();
                    $data['password'] = Hash::make($request->new_password);
                    Customers::where('id', $customer['id'])->update($data);
                    // MAIL NOTIFICATION
                    $full_name  = $customer->full_name;
                    $email      = $customer->email;
                    $data = array('subject' => "Change Password", 'email' => $email, 'name' => $full_name, 'page' => 'email.api.changepassword');
                    Mail::send($data['page'], $data, function ($message) use ($data) {
                        $message->to($data['email'])->subject($data['subject']);
                    });
                    return response()->json(['status' => true, 'message' => 'Password Reset Successfully']);
                } else {
                    return response()->json(['status' => false, 'message' => 'Wrong OTP']);
                }
            } else {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }
   
}