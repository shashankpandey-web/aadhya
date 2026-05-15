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
use Illuminate\Support\Str;
use App\Models\Customers;
use App\Models\Countries;
use App\Models\States;
use App\Models\City;
use App\Models\Categories;
use App\Models\Availability;
use App\Models\Apprating;
use App\Models\Contactus;
use App\Models\Advisorreviews;
use App\Models\CustomerCategories;

class CommonController extends Controller
{

    public function countries(Request $request)
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

            $country_arrs  = array();
            $get_countries = Countries::get();
            foreach ($get_countries as $key => $value) {
                $country  = array();
                $country['id']     = $value->id;
                $country['name']   = $value->name;
                $country_arrs[] = $country;
            }
            return response()->json(['status' => true, 'message' => 'Country list', 'data' => $country_arrs]);
            die();
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die();
        }
    }

    public function states(Request $request)
    {

        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'country_id'  => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $states  = array();
            $get_states = States::where(['country_id' => $request->country_id])->get();
            foreach ($get_states as $key => $value) {
                $state  = array();
                $state['id']          = $value->id;
                $state['name']        = $value->name;
                $state['country_id']  = $value->country_id;
                $states[] = $state;
            }
            return response()->json(['status' => true, 'message' => 'State list', 'data' => $states]);
            die();
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die();
        }
    }

    public function cities(Request $request)
    {

        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'state_id'   => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $get_cities = City::where(['state_id' => $request->state_id])->get();
            $cities  = array();
            foreach ($get_cities as $key => $value) {
                $city  = array();
                $city['id']        = $value->id;
                $city['name']      = $value->name;
                $city['state_id']  = $value->state_id;
                $cities[]          = $city;
            }
            return response()->json(['status' => true, 'message' => 'City List', 'data' => $cities]);
            die();
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die();
        }
    }

    public function categories(Request $request)
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

            $category_arrs  = array();
            //$get_categories = Categories::whereNull('is_delete')->orderBy('title', 'asc')->get();
            $get_categories = Categories::whereNull('is_delete')->orderBy('id', 'desc')->get();
            foreach ($get_categories as $key => $value) {
                $category          = array();
                $category['id']    = $value->id;
                $category['title'] = isset($value->title) ? $value->title : '';
                $category['image'] = $value->image ? url('uploads/category/' . $value->image) : '';
                $category_arrs[]   = $category;
            }
            return response()->json(['status' => true, 'message' => 'Category list', 'data' => $category_arrs]);
            die();
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die();
        }
    }

    public function availabilities(Request $request)
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

            $availability_arrs  = array();
            $get_availabilities = Availability::get();
            foreach ($get_availabilities as $key => $value) {
                $availability  = array();
                $availability['id']         = $value->id;
                $availability['title']      = isset($value->title) ? $value->title : '';
                $availability['sub_title']  = isset($value->sub_title) ? $value->sub_title : '';
                $availability['image']      = $value->image ? url('uploads/availability/' . $value->image) :'';
                $availability_arrs[]        = $availability;
            }
            return response()->json(['status' => true, 'message' => 'Availability list', 'data' => $availability_arrs]);
            die();
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die();
        }
    }

    public function app_rating(Request $request)
    {
        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'app_rating'  => 'required',
                'app_review'  => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth      = Auth::guard('api')->user();
            $Apprating = Apprating::where(['id' => $Auth->id])->first();
            if (! $Apprating) {
                $Apprating              = new Apprating();
                $Apprating->customer_id = $Auth->id;
                $Apprating->app_rating  = $request->app_rating;
                $Apprating->app_review  = isset($request->app_review) ? $request->app_review : '';
                $added_apprating        = $Apprating->save();
                if ($added_apprating) {
                    $message  = 'Thank you for your valueble feedback';
                }
            } else {
                $message  = 'Feedback already given';
            }
            return response()->json(['status' => true, 'message' => $message]);
            die();
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            die();
        }
    }

    public function contact_us(Request $request)
    {

        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'subject'     => 'required|min:5|max:100',
                'email'       => 'required|email',
                'message'     => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth = Auth::guard('api')->user();
            $Contactus = new Contactus();
            $Contactus->customer_id = $Auth->id;
            $Contactus->subject     = $request->subject;
            $Contactus->email       = $request->email;
            $Contactus->message     = $request->message;
            $added_contactus        = $Contactus->save();
            if ($added_contactus) {
                $message  = 'Thank you for contact us';

                $support_site_email = get_setting_data('support_site_email', 'content'); 

                $subject = $request->subject;
                $email   = $request->email;
                $message_text = $request->message;
                $data = array('subject' => "Contact Us", 'support_site_email' => $support_site_email,'email' => $email, 'subject_text' => $subject, 'message_text' => $message_text, 'page' => 'email.api.contactus');
                
                Mail::send($data['page'], $data, function ($message) use ($data) {
                    $message->to($data['support_site_email'])->subject($data['subject']);
                });

            } else {
                $message  = 'something went wrong';
            }
            return response()->json(['status' => true, 'message' => $message]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function add_advisor_review(Request $request)
    {

        try {
            // Validate the request
            $err = [
                'device_id'     => 'required',
                'advisore_id'   => 'required',
                'rating'        => 'required|numeric|min:1|max:5',
                'rating_text'   => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth = Auth::guard('api')->user();
            $advisore = Customers::where(['id' => $request->advisore_id, 'status' => 'Active', 'type' => 'Advisor'])->whereNull('is_delete')->first();
            if ($advisore) {
                $Advisorreview = new Advisorreviews();
                $Advisorreview->customer_id = $Auth->id;
                $Advisorreview->advisore_id = $request->advisore_id;
                $Advisorreview->rating      = $request->rating;
                $Advisorreview->rating_text = $request->rating_text;
                $Advisorreview->device_id   = $request->device_id;
                $added_advisor_review       = $Advisorreview->save();
                if ($added_advisor_review) {


                    $review_sum = 0;
                    $get_advisores = Advisorreviews::where(['status' => 'Active'])->where(['advisore_id' => $request->advisore_id])->get();
                    if (count($get_advisores) > 0) {
                        foreach ($get_advisores as $key => $value) {
                            $review_sum += $value['rating'];
                        }
                    }
                    //$output['total_review']  = $review_sum;
                    //$output['count']  = count($get_advisores);
                    if (count($get_advisores) > 0 && $review_sum > 0) {
                        $average_rating = $review_sum / count($get_advisores);
                        //$output['average_rating']  = $average_rating;
                        $advisore->average_rating = $average_rating;
                        $advisore->save();
                    }
                    return response()->json(['status' => true, 'message' => 'Review Submited Successfully']);
                    die();
                }
            } else {
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 422);
                die();
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function advisor_reviews(Request $request)
    {
        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
                'advisore_id'   => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth = Auth::guard('api')->user();
            $data = [];
            $advisore_review_arrs = array();
            $advisore = Customers::where(['id' => $request->advisore_id])->first();
            if ($advisore) {
                $data['average_review']   = $advisore->average_rating;
                $data['name']             = $advisore->full_name;
                $data['is_online']        = ($advisore->is_online==1) ? true : false;
                $data['is_busy']        = ($advisore->is_busy==1) ? true : false;
                $data['profile']          = $advisore->image ? url('uploads/image/' . $advisore->image) : url('uploads/placeholder/dummy_image.png');
                $data['screen_name']      = $advisore->screen_name;
                $data['service_name']     = $advisore->service_name;

                /*$get_categories = json_decode($advisore->categories, true);
                $category_arr = [];
                if( is_array($get_categories) ){
                    $get_categories = Categories::whereIn('id', $get_categories)->get();
                    if( count($get_categories) > 0 ){
                        foreach ($get_categories as $cat_key => $cat_value) {
                            $category_arr[] = $cat_value['title'];
                        }
                    }
                }else{
                    $category_arr[] = 'N/A';
                }
                $data['categories']     = implode(', ', $category_arr);*/

                $customer_category_arr = [];
                $get_customer_categories = CustomerCategories::where(['customer_id' => $request->advisore_id])->get();
                if ($get_customer_categories) {
                    foreach ($get_customer_categories as $key => $value) {
                        $get_customer_category = Categories::where(['id' => $value->caterogy_id])->first();
                        if ($get_customer_category) {
                            $customer_category_arr[] = $get_customer_category->title;
                        }
                    }
                }
                $data['category']               = implode(', ', $customer_category_arr);

                $get_advisore_reviews = Advisorreviews::where(['status' => 'Active'])->orderBy('id', 'desc')->where(['advisore_id' => $request->advisore_id])->get();
                $data['total_review'] = count($get_advisore_reviews);
                if (count($get_advisore_reviews) > 0) {
                    foreach ($get_advisore_reviews as $key => $value) {
                        $advisore_review_arr  = array();
                        $advisore_review_arr['id']             = $value->id;
                        $customer = Customers::where(['id' => $value->customer_id])->first();
                        if ($customer) {
                            $advisore_review_arr['customer_name']  = $customer->full_name;
                            $advisore_review_arr['is_online']    = ($customer->is_online==1) ? true : false;
                            $advisore_review_arr['is_busy']    = ($customer->is_busy==1) ? true : false;
                            $advisore_review_arr['image']      = $customer->image ? url('uploads/image/' . $customer->image) : url('uploads/placeholder/dummy_image.png');
                        }
                        $advisore_review_arr['rating']         = $value->rating;
                        $advisore_review_arr['rating_text']    = $value->rating_text;
                        $advisore_review_arrs[] = $advisore_review_arr;
                    }
                }
            }
            $data['customers'] = $advisore_review_arrs;
            $output['status']   = true;
            $output['message']  = 'Advisor Review list';

            return response()->json(['status' => true, 'message' => 'Profile', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function sale_status(Request $request)
    {
        $enabled = get_setting_data('enable_twice_monthly_sale', 'content') === 'active';

        if (!$enabled) {
            return response()->json([
                'status'  => true,
                'message' => 'Sale status fetched',
                'data'    => [
                    'is_sale_active'       => false,
                    'discount_percentage'  => 0,
                    'custom_message'       => '',
                    'active_period'        => null,
                ],
            ]);
        }

        $today = (int) date('j');

        $p1_start = (int) get_setting_data('sale_period_1_start_day', 'content');
        $p1_end   = (int) get_setting_data('sale_period_1_end_day',   'content');
        $p2_start = (int) get_setting_data('sale_period_2_start_day', 'content');
        $p2_end   = (int) get_setting_data('sale_period_2_end_day',   'content');

        $in_period_1 = $today >= $p1_start && $today <= $p1_end;
        $in_period_2 = $today >= $p2_start && $today <= $p2_end;
        $is_active   = $in_period_1 || $in_period_2;

        $active_period = null;
        if ($in_period_1) {
            $active_period = ['start_day' => $p1_start, 'end_day' => $p1_end, 'period' => 1];
        } elseif ($in_period_2) {
            $active_period = ['start_day' => $p2_start, 'end_day' => $p2_end, 'period' => 2];
        }

        return response()->json([
            'status'  => true,
            'message' => 'Sale status fetched',
            'data'    => [
                'is_sale_active'      => $is_active,
                'discount_percentage' => $is_active ? (int) get_setting_data('monthly_sale_discount_percentage', 'content') : 0,
                'custom_message'      => $is_active ? get_setting_data('sale_custom_message', 'content') : '',
                'active_period'       => $active_period,
            ],
        ]);
    }
}
