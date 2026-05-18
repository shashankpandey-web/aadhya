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
use App\Models\Coupon;
use App\Models\Logs;
use Illuminate\Support\Facades\Http;
use TypeError;
use Exception;
use App\Traits\CashfreeTraits;



class HomeController extends Controller
{

    use CashfreeTraits;
    public function customer_home(Request $request)
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

            $data = array();
            $Auth = Auth::guard('api')->user();
            /* Home Page Top Rated Advisors List */
            $get_top_rated_advisors = Customers::orderBy('average_rating', 'desc');
            $get_top_rated_advisors->where(['status' => 'Active', 'type' => 'Advisor']);
            $get_top_rated_advisors->whereBetween('average_rating', [4, 5]);
            $get_top_rated_advisors->whereNull('step');
            $get_top_rated_advisors->whereNull('is_delete');
            $get_top_rated_advisors->limit(4);
            $get_top_rated_advisors = $get_top_rated_advisors->get();
            $data['top_rated_advisors'] = $this->get_advisores($get_top_rated_advisors, $Auth->id);
            /* Home Page Most happening Advisors List */

            $get_most_happening_advisores = Customers::orderBy('id', 'desc')
                ->where(['status' => 'Active', 'type' => 'Advisor'])
                ->whereNull('step')
                ->whereNull('is_delete')
                ->limit(4)
                ->get();
            $data['most_happening_advisores'] = $this->get_advisores($get_most_happening_advisores, $Auth->id);

            /* Home Page New Advisors List */
            $get_new_advisores     = Customers::orderBy('id', 'desc')->where(['status' => 'Active', 'type' => 'Advisor'])->whereNull('step')->whereNull('is_delete')->limit(10)->get();
            $data['new_advisores'] = $this->get_advisores($get_new_advisores, $Auth->id);

            /* Home Page All Advisors List */
            $get_advisores         = Customers::orderBy('id', 'desc')->where(['status' => 'Active', 'type' => 'Advisor'])->whereNull('step')->whereNull('is_delete')->get();
            $data['all_advisores'] = $this->get_advisores($get_advisores, $Auth->id);

            /* Home Page Availability List */
            $get_availabilities = Availability::get();
            $availability_arrs  = array();
            foreach ($get_availabilities as $key => $value) {
                $availability  = array();
                $availability['id']         = $value->id;
                $availability['title']      = $value->title;
                $availability['sub_title']  = $value->sub_title;
                $availability['image']      = $value->image ? url('uploads/availability/' . $value->image) : url('uploads/placeholder/dummy_image.png');;
                $availability_arrs[]        = $availability;
            }
            $data['availabilities'] = $availability_arrs;
            return response()->json(['status' => true, 'message' => 'Customer Home Page', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function all_advisores(Request $request)
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

            $data = array();
            $Auth = Auth::guard('api')->user();

            $sale_active      = false;
            $sale_discount_pct = 0;
            if (get_setting_data('enable_twice_monthly_sale', 'content') === 'active') {
                $today = (int) date('j');
                $p1s   = (int) get_setting_data('sale_period_1_start_day', 'content');
                $p1e   = (int) get_setting_data('sale_period_1_end_day', 'content');
                $p2s   = (int) get_setting_data('sale_period_2_start_day', 'content');
                $p2e   = (int) get_setting_data('sale_period_2_end_day', 'content');
                if (($today >= $p1s && $today <= $p1e) || ($today >= $p2s && $today <= $p2e)) {
                    $sale_active       = true;
                    $sale_discount_pct = (int) get_setting_data('monthly_sale_discount_percentage', 'content');
                }
            }

            $setLimit     = config('apiconfig.records_per_page');
            $offset       = 0;
            if ($request->offset) {
                $offset   = $request->offset-1;
            }
            $limit        = $offset * $setLimit;

            $sql = '';
            if ($request->type == 'top_rated') {
                $get_top_rated_advisors = DB::table('customers AS c');
                $get_top_rated_advisors->orderBy('c.id', 'desc');
                $get_top_rated_advisors->select('c.*');
                if ($request->availability_id) {
                    $get_top_rated_advisors->join('customer_availabilities AS ca', 'c.id', '=', 'ca.customer_id');
                }
                if ($request->category_id) {
                    $get_top_rated_advisors->join('customer_categories AS cc', 'c.id', '=', 'cc.customer_id');
                }
                $get_top_rated_advisors->where(['c.status' => 'Active', 'c.type' => 'Advisor']);
                $get_top_rated_advisors->whereNull('step');
                $get_top_rated_advisors->whereNull('is_delete');
                if ($request->availability_id) {
                    $get_top_rated_advisors->whereIn('ca.availability_id', $request->availability_id);
                }
                if ($request->category_id) {
                    $get_top_rated_advisors->whereIn('cc.caterogy_id', $request->category_id);
                }
                if ($request->rating) {
                    $get_top_rated_advisors->where('c.average_rating', $request->rating);
                } else {
                    $get_top_rated_advisors->whereBetween('average_rating', [4, 5]);
                }
                $TotalCount = count($get_top_rated_advisors->get());
                $get_advisors_list =  $get_top_rated_advisors->groupBy('c.id')->offset($limit)->take($setLimit)->get();
                $message = 'Top rated Advisores';
            } elseif ($request->type == 'most_happening_advisore') {
                $get_most_happening_advisores = DB::table('customers AS c');
                $get_most_happening_advisores->orderBy('c.id', 'desc');
                $get_most_happening_advisores->select('c.*');
                if ($request->availability_id) {
                    $get_most_happening_advisores->join('customer_availabilities AS ca', 'c.id', '=', 'ca.customer_id');
                }
                if ($request->category_id) {
                    $get_most_happening_advisores->join('customer_categories AS cc', 'c.id', '=', 'cc.customer_id');
                }
                $get_most_happening_advisores->where(['c.status' => 'Active', 'c.type' => 'Advisor']);
                $get_most_happening_advisores->whereNull('step');
                $get_most_happening_advisores->whereNull('is_delete');
                if ($request->availability_id) {
                    $get_most_happening_advisores->whereIn('ca.availability_id', $request->availability_id);
                }
                if ($request->category_id) {
                    $get_most_happening_advisores->whereIn('cc.caterogy_id', $request->category_id);
                }
                if ($request->rating) {
                    $get_most_happening_advisores->where('c.average_rating', $request->rating);
                }
                $get_most_happening_advisores->groupBy('c.id')->get();
                $TotalCount = count($get_most_happening_advisores->get());
                $get_advisors_list = $get_most_happening_advisores->groupBy('c.id')->offset($limit)->take($setLimit)->get();
                $message           = 'Most Happening Advisores';
            } elseif ($request->type == 'new_advisore') {
                $new_advisores = DB::table('customers AS c');
                $new_advisores->orderBy('c.id', 'desc');
                $new_advisores->select('c.*');
                if ($request->availability_id) {
                    $new_advisores->join('customer_availabilities AS ca', 'c.id', '=', 'ca.customer_id');
                }
                if ($request->category_id) {
                    $new_advisores->join('customer_categories AS cc', 'c.id', '=', 'cc.customer_id');
                }
                $new_advisores->where(['c.status' => 'Active', 'c.type' => 'Advisor']);
                $new_advisores->whereNull('step');
                $new_advisores->whereNull('is_delete');
                if ($request->availability_id) {
                    $new_advisores->whereIn('ca.availability_id', $request->availability_id);
                }
                if ($request->category_id) {
                    $new_advisores->whereIn('cc.caterogy_id', $request->category_id);
                }
                if ($request->rating) {
                    $new_advisores->where('c.average_rating', $request->rating);
                }
                $TotalCount = count($new_advisores->get());
                $get_advisors_list = $new_advisores->groupBy('c.id')->offset($limit)->take($setLimit)->get();
                $message = 'All New Advisores';
            } else {
                $get_all_advisores = DB::table('customers AS c');
                $get_all_advisores->orderBy('c.id', 'desc');
                $get_all_advisores->select('c.*');
                if ($request->availability_id) {
                    $get_all_advisores->join('customer_availabilities AS ca', 'c.id', '=', 'ca.customer_id');
                }
                if ($request->category_id) {
                    $get_all_advisores->join('customer_categories AS cc', 'c.id', '=', 'cc.customer_id');
                }
                $get_all_advisores->where(['c.status' => 'Active', 'c.type' => 'Advisor']);
                $get_all_advisores->whereNull('step');
                $get_all_advisores->whereNull('is_delete');
                if ($request->availability_id) {
                    $get_all_advisores->whereIn('ca.availability_id', $request->availability_id);
                }
                if ($request->category_id) {
                    $get_all_advisores->whereIn('cc.caterogy_id', $request->category_id);
                }
                if ($request->rating) {
                    $get_all_advisores->where('c.average_rating', $request->rating);
                }
                $TotalCount = count($get_all_advisores->get());
                $get_advisors_list = $get_all_advisores->groupBy('c.id')->offset($limit)->take($setLimit)->get();
                $message = 'All Advisore list';
            }

            $data = array();
            if (count($get_advisors_list) > 0) {
                foreach ($get_advisors_list as $key => $value) {
                    $advisore  = array();
                    $advisore['id']             = $value->id;
                    $advisore['is_wishlist']    = false;
                    $customer_wishlist = Customerwishlist::where(['customer_id' => $Auth->id, 'advisore_id' => $value->id])->first();
                    if ($customer_wishlist) {
                        $advisore['is_wishlist'] = true;
                    }
                    $advisore['full_name']    = isset($value->full_name) ? $value->full_name : '';
                    $advisore['is_online']             = ($value->is_online==1) ? true : false;
                    $advisore['is_busy']             = ($value->is_busy==1) ? true : false;
                    $advisore['image']        = $value->image ? url('uploads/image/' . $value->image) : url('uploads/placeholder/dummy_image.png');
                    $advisore['screen_name']  = isset($value->screen_name) ? $value->screen_name : '';
                    $advisore['service_name'] = isset($value->service_name) ? $value->service_name : '';
                    $advisore['rating']       = isset($value->average_rating) ? $value->average_rating : '';
                    $advisore['total_orders'] = Orders::where(['advisore_id' => $value->id])->count();
                    $category_arrs            = [];
                    $CustomerCategories       = CustomerCategories::where(['customer_id' => $value->id])->get();
                    if (count($CustomerCategories) > 0) {
                        foreach ($CustomerCategories as $cus_cat_key => $cus_cat_value) {
                            $get_category = Categories::where(['id' => $cus_cat_value['caterogy_id']])->first();
                            if ($get_category) {
                                $category  = array();
                                $category['id']    = $get_category->id;
                                $category['title'] = isset($get_category->title) ? $get_category->title : '';
                                $category_arrs[]   = $category;
                            }
                        }
                    }
                    $advisore['categories'] = $category_arrs;

                    // $availability_arrs  = array();
                    // $CustomerAvailabilities = CustomerAvailabilities::where(['customer_id' => $value->id, 'status' => 'Active'])->get();
                    // foreach ($CustomerAvailabilities as $key => $value) {
                    //     $get_availability                = Availability::where(['id' => $value->availability_id])->first();
                    //     $availability                    = array();
                    //     $availability['id']              = $value->id;
                    //     $availability['availability_id'] = $value->availability_id;
                    //     $availability['title']           = isset($get_availability->title) ? $get_availability->title : '';
                    //     $availability['sub_title']       = isset($get_availability->sub_title) ? $get_availability->sub_title : '';
                    //     $availability['image']           = $get_availability->image ? url('uploads/availability/' . $get_availability->image) : url('uploads/placeholder/dummy_image.png');
                    //     $availability['charges']         = $value->charges ? $value->charges : 0;
                    //     // if ($value->charges != '' && $value->charges_type != '') {
                    //     //     //$availability['charges']  = $value->charges.' / '.$value->charges_type;
                    //     //     $availability['charges']  = $value->charges;
                    //     // } else {
                    //     //     $availability['charges']  = '0';
                    //     // }
                    //     $availability_arrs[]        = $availability;
                    // }


                    $availability_arrs  = array();
                    $get_availability = Availability::where(['status' => 'Active'])->whereNull('is_delete')->orderBy('id', 'desc')->get();
                    foreach ($get_availability as $key => $value1) {
                        $CustomerAvailabilities = CustomerAvailabilities::where(['availability_id' => $value1->id,'customer_id' => $value->id])->first();
                        $availability                    = array();
                        $availability['availability_id'] = $value1->id;
                        $availability['title']           = isset($value1->title) ? $value1->title : '';
                        $availability['sub_title']       = isset($value1->sub_title) ? $value1->sub_title : '';
                        $availability['image']           = $value1->image ? url('uploads/availability/' . $value1->image) : url('uploads/placeholder/dummy_image.png');

                        $availability['id']              = NULL;
                        $availability['charges']         = 0;
                        $availability['status']       = 'Deactive';

                        if ($CustomerAvailabilities) {
                            $availability['id']              = $CustomerAvailabilities->id;
                            $availability['charges']         = $CustomerAvailabilities->charges ? $CustomerAvailabilities->charges : 0;
                            $availability['status']       = isset($CustomerAvailabilities->status) ? $CustomerAvailabilities->status : '';
                        }

                        if ($sale_active && floatval($availability['charges']) > 0) {
                            $original = floatval($availability['charges']);
                            $discount = ($original / 100) * $sale_discount_pct;
                            $discount = min($discount, $original);
                            $availability['original_charges']   = $original;
                            $availability['discount_amount']    = round($discount, 2);
                            $availability['discounted_charges'] = round($original - $discount, 2);
                        }

                        $availability_arrs[]        = $availability;
                    }
                    $advisore['availabilities'] = $availability_arrs;
                    $data[] = $advisore;
                }
            }

            return response()->json(['status' => true, 'message' => $message, 'data' => $data, 'page_count' => ceil($TotalCount / $setLimit), 'total_count' => $TotalCount]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized', $e->getMessage()], 401);
        }
    }

    public function advisore_detail(Request $request)
    {

        try {
            // Validate the request
            $err = [
                'device_id'     => 'required',
                'advisore_id'   => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth = Auth::guard('api')->user();

            // Resolve monthly sale status
            $sale_active_detail      = false;
            $sale_discount_pct_detail = 0;
            if (get_setting_data('enable_twice_monthly_sale', 'content') === 'active') {
                $today = (int) date('j');
                $p1s   = (int) get_setting_data('sale_period_1_start_day', 'content');
                $p1e   = (int) get_setting_data('sale_period_1_end_day', 'content');
                $p2s   = (int) get_setting_data('sale_period_2_start_day', 'content');
                $p2e   = (int) get_setting_data('sale_period_2_end_day', 'content');
                if (($today >= $p1s && $today <= $p1e) || ($today >= $p2s && $today <= $p2e)) {
                    $sale_active_detail       = true;
                    $sale_discount_pct_detail = (int) get_setting_data('monthly_sale_discount_percentage', 'content');
                }
            }

            // Resolve optional coupon for discounted pricing preview
            $coupon_discount_meta = null;
            if ($request->coupon_code) {
                $AppliedCoupon = Coupon::where('code', trim($request->coupon_code))
                    ->where('status', 'Active')
                    ->whereNull('is_delete')
                    ->first();

                if ($AppliedCoupon) {
                    $now     = now()->toDateTimeString();
                    $usageOk = true;

                    if ($AppliedCoupon->start_date && $AppliedCoupon->start_date > $now)   $usageOk = false;
                    if ($AppliedCoupon->expiry_date && $AppliedCoupon->expiry_date < $now)  $usageOk = false;

                    if ($usageOk && $AppliedCoupon->usage_limit_per_coupon) {
                        $total_uses = Orders::where('coupon_code', $AppliedCoupon->code)->count();
                        if ($total_uses >= intval($AppliedCoupon->usage_limit_per_coupon)) $usageOk = false;
                    }

                    if ($usageOk && $AppliedCoupon->usage_limit_per_user) {
                        $user_uses = Orders::where('coupon_code', $AppliedCoupon->code)
                            ->where('customer_id', $Auth->id)->count();
                        if ($user_uses >= intval($AppliedCoupon->usage_limit_per_user)) $usageOk = false;
                    }

                    if ($usageOk && $AppliedCoupon->is_first_order_offer) {
                        if (Orders::where('customer_id', $Auth->id)->exists()) $usageOk = false;
                    }

                    if ($usageOk) {
                        $coupon_discount_meta = $AppliedCoupon;
                    }
                }
            }

            $advisore  = array();
            $get_advisore = Customers::where(['id' => $request->advisore_id, 'status' => 'Active', 'type' => 'Advisor'])->whereNull('is_delete')->first();
            if ($get_advisore) {
                $advisore['id']             = $get_advisore->id;

                $advisore['is_wishlist']    = false;
                $customer_wishlist = Customerwishlist::where(['customer_id' => $Auth->id, 'advisore_id' => $request->advisore_id])->first();
                if ($customer_wishlist) {
                    $advisore['is_wishlist'] = true;
                }

                $advisore['full_name']    = isset($get_advisore->full_name) ? $get_advisore->full_name : '';
                $advisore['is_online']    = ($get_advisore->is_online==1) ? true : false;
                $advisore['is_busy']    = ($get_advisore->is_busy==1) ? true : false;
                $advisore['image']        = $get_advisore->image ? url('uploads/image/' . $get_advisore->image) : url('uploads/placeholder/dummy_image.png');
                $advisore['screen_name']  = isset($get_advisore->screen_name) ? $get_advisore->screen_name : '';
                $advisore['service_name'] = isset($get_advisore->service_name) ? $get_advisore->service_name : '';

                $category_arrs = [];
                $CustomerCategories = CustomerCategories::where(['customer_id' =>  $get_advisore->id])->get();
                if (count($CustomerCategories) > 0) {
                    foreach ($CustomerCategories as $cus_cat_key => $cus_cat_value) {
                        $get_category = Categories::where(['id' => $cus_cat_value['caterogy_id']])->first();
                        if ($get_category) {
                            $category          = array();
                            $category['id']    = $get_category->id;
                            $category['title'] = isset($get_category->title) ? $get_category->title : '';
                            $category_arrs[]   = $category;
                        }
                    }
                }
                $advisore['categories'] = $category_arrs;


                /*$get_categories = json_decode($get_advisore->categories, true);
                $category_arr = [];
                if( is_array($get_categories) ){
                    $get_categories = Categories::whereIn('id', $get_categories)->get();
                    if( count($get_categories) > 0 ){
                        foreach ($get_categories as $cat_key => $cat_value) {
                            $category_arr[] = $cat_value['title'];
                        }
                        $advisore['categories']     = implode(', ', $category_arr);
                    }
                }else{
                    $category_arr[] = 'N/A';
                }*/

                /*$CustomerAvailabilities = CustomerAvailabilities::where(['customer_id' => $get_advisore->id, 'status' => 'Active'])->get();
                $availability_arrs  = array();
                foreach ($CustomerAvailabilities as $key => $value) {
                    $get_availability                = Availability::where(['id' => $value->availability_id])->first();
                    $availability                    = array();
                    $availability['id']              = $value->id;
                    $availability['availability_id'] = $value->availability_id;
                    $availability['title']           = isset($get_availability->title) ? $get_availability->title : '';
                    $availability['sub_title']       = isset($get_availability->sub_title) ? $get_availability->sub_title : '';
                    $availability['image']           = $get_availability->image ? url('uploads/availability/' . $get_availability->image) : url('uploads/placeholder/dummy_image.png');
                    $availability['charges']         = $value->charges ? $value->charges : 0;
                    // if ($value->charges != '' && $value->charges_type != '') {
                    //     $availability['charges']  = $value->charges;
                    // } else {
                    //     $availability['charges']  = '0';
                    // }
                    $availability_arrs[]        = $availability;
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

                    $CustomerAvailabilities = CustomerAvailabilities::where(['availability_id' => $value1->id,'customer_id' => $get_advisore->id])->first();
                    if ($CustomerAvailabilities) {
                        $availability['id']      = $CustomerAvailabilities->id;
                        $availability['charges'] = $CustomerAvailabilities->charges ? $CustomerAvailabilities->charges : 0;
                        $availability['status']  = isset($CustomerAvailabilities->status) ? $CustomerAvailabilities->status : '';
                    }

                    if ($coupon_discount_meta && floatval($availability['charges']) > 0) {
                        $original = floatval($availability['charges']);

                        if ($coupon_discount_meta->type == 'percentage') {
                            $discount = ($original / 100) * floatval($coupon_discount_meta->value);
                        } else {
                            $discount = floatval($coupon_discount_meta->value);
                        }

                        if ($coupon_discount_meta->maximum_amount && $discount > floatval($coupon_discount_meta->maximum_amount)) {
                            $discount = floatval($coupon_discount_meta->maximum_amount);
                        }

                        $discount = min($discount, $original);

                        $availability['original_charges']   = $original;
                        $availability['discount_amount']    = round($discount, 2);
                        $availability['discounted_charges'] = round($original - $discount, 2);
                    } elseif ($sale_active_detail && floatval($availability['charges']) > 0) {
                        $original = floatval($availability['charges']);
                        $discount = ($original / 100) * $sale_discount_pct_detail;
                        $discount = min($discount, $original);
                        $availability['original_charges']   = $original;
                        $availability['discount_amount']    = round($discount, 2);
                        $availability['discounted_charges'] = round($original - $discount, 2);
                    }

                    $availability_arrs[]        = $availability;
                }

                
                $advisore['availabilities'] = $availability_arrs;

                $advisore['about_my_service']       = isset($get_advisore->about_my_service) ? $get_advisore->about_my_service : '';
                $advisore['about_me']               = isset($get_advisore->about_me) ? $get_advisore->about_me : '';
                $advisore['ordering_instructions']  = isset($get_advisore->ordering_instructions) ? $get_advisore->ordering_instructions : '';
                $advisore['my_video']               = $get_advisore->my_video ? get_image_upload_s3($get_advisore->my_video) : '';
                $advisore['average_rating']         = isset($get_advisore->average_rating) ? (float)$get_advisore->average_rating : '';
                $advisore['total_orders']           = Orders::where(['advisore_id' => $request->advisore_id])->count();
                $advisore['year_joined']            = date('Y', strtotime($get_advisore->created_at));


                /* All Reviews of Advisore */
                $get_advisore_reviews = Advisorreviews::where(['status' => 'Active'])->orderBy('id', 'desc')->where(['advisore_id' => $request->advisore_id])->get();
                $output['total_review'] = count($get_advisore_reviews);
                $advisore_review_arrs = [];
                if (count($get_advisore_reviews) > 0) {
                    foreach ($get_advisore_reviews as $key => $value) {
                        $advisore_review_arr                = array();
                        $advisore_review_arr['review_id']   = $value->id;
                        $advisore_review_arr['customer_id'] = $value->customer_id;
                        $customer                           = Customers::where(['id' => $value->customer_id])->first();
                        if ($customer) {
                            $advisore_review_arr['customer_name'] = isset($customer->full_name) ? $customer->full_name : '';
                            $advisore_review_arr['is_online']    = ($customer->is_online==1) ? true : false;
                            $advisore_review_arr['is_busy']    = ($customer->is_busy==1) ? true : false;
                            $advisore_review_arr['image']         = $customer->image ? url('uploads/image/' . $customer->image) : url('uploads/placeholder/dummy_image.png');
                        }
                        $advisore_review_arr['review_date'] = date('M d, Y h:i A', strtotime($customer->created_at));
                        $advisore_review_arr['rating']      = isset($value->rating) ? $value->rating : '';
                        $advisore_review_arr['rating_text'] = isset($value->rating_text) ? $value->rating_text : '';
                        $advisore_review_arrs[]             = $advisore_review_arr;
                    }
                }
                $advisore['all_review'] = $advisore_review_arrs;


                /* Positive Reviews of Advisore*/
                $postive_advisore_reviews = Advisorreviews::where(['status' => 'Active'])->orderBy('id', 'desc')
                    ->where(['advisore_id' => $request->advisore_id])
                    ->whereBetween('rating', [4, 5]);

                $advisore['like_count']   = $postive_advisore_reviews->count();
                $postive_advisore_reviews = $postive_advisore_reviews->get();

                $output['total_positive_review'] = count($postive_advisore_reviews);
                $advisore_review_arrs = [];
                if (count($postive_advisore_reviews) > 0) {
                    foreach ($postive_advisore_reviews as $key => $value) {
                        $advisore_review_arr                = array();
                        $advisore_review_arr['review_id']   = $value->id;
                        $advisore_review_arr['customer_id'] = $value->customer_id;
                        $customer                           = Customers::where(['id' => $value->customer_id])->first();
                        if ($customer) {
                            $advisore_review_arr['customer_name'] = isset($customer->full_name) ? $customer->full_name  : '';
                            $advisore_review_arr['is_online']    = ($customer->is_online==1) ? true : false;
                            $advisore_review_arr['is_busy']    = ($customer->is_busy==1) ? true : false;
                            $advisore_review_arr['image']         = $customer->image ? url('uploads/image/' . $customer->image) : url('uploads/placeholder/dummy_image.png');
                        }
                        $advisore_review_arr['review_date'] = date('M d, Y h:i A', strtotime($customer->created_at));
                        $advisore_review_arr['rating']      = isset($value->rating) ? $value->rating : '';
                        $advisore_review_arr['rating_text'] = isset($value->rating_text) ? $value->rating_text : '';
                        $advisore_review_arrs[]             = $advisore_review_arr;
                    }
                }
                $advisore['positive_review'] = $advisore_review_arrs;


                /* Positive Reviews of Advisore*/
                $negative_advisore_reviews = Advisorreviews::where(['status' => 'Active'])->orderBy('id', 'desc')
                    ->where(['advisore_id' => $request->advisore_id])
                    ->whereBetween('rating', [1, 3.9]);

                $advisore['dislike_count'] = $negative_advisore_reviews->count();
                $negative_advisore_reviews = $negative_advisore_reviews->get();

                $output['total_negative_review'] = count($negative_advisore_reviews);
                $advisore_review_arrs = [];
                if (count($negative_advisore_reviews) > 0) {
                    foreach ($negative_advisore_reviews as $key => $value) {
                        $advisore_review_arr                = array();
                        $advisore_review_arr['review_id']   = $value->id;
                        $advisore_review_arr['customer_id'] = $value->customer_id;
                        $customer                           = Customers::where(['id' => $value->customer_id])->first();
                        if ($customer) {
                            $advisore_review_arr['customer_name'] = isset($customer->full_name) ? $customer->full_name : '';
                            $advisore_review_arr['is_online']    = ($customer->is_online==1) ? true : false;
                            $advisore_review_arr['is_busy']    = ($customer->is_busy==1) ? true : false;
                            $advisore_review_arr['image']         = $customer->image ? url('uploads/image/' . $customer->image) : url('uploads/placeholder/dummy_image.png'); 
                        }
                        $advisore_review_arr['review_date']    = date('M d, Y h:i A', strtotime($customer->created_at));
                        $advisore_review_arr['rating']         = isset($value->rating) ? $value->rating : '';
                        $advisore_review_arr['rating_text']    = isset($value->rating_text) ? $value->rating_text : '';
                        $advisore_review_arrs[] = $advisore_review_arr;
                    }
                }
                $advisore['negative_review'] = $advisore_review_arrs;
            }
            return response()->json(['status' => true, 'message' => 'Advisore Detail Page', 'data' => $advisore]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    private function get_advisores($advisor_arr = [], $customer_id = '')
    {
        $advisore_arrs = array();
        if (count($advisor_arr) > 0) {
            foreach ($advisor_arr as $key => $value) {

                $advisore  = array();
                $advisore['id']             = $value->id;
                $advisore['is_wishlist']    = false;
                $customer_wishlist = Customerwishlist::where(['customer_id' => $customer_id, 'advisore_id' => $value->id])->first();
                if ($customer_wishlist) {
                    $advisore['is_wishlist'] = true;
                }
                $advisore['full_name']    = isset($value->full_name) ? $value->full_name : '';
                $advisore['is_online']    = ($value->is_online==1) ? true : false;
                $advisore['is_busy']    = ($value->is_busy==1) ? true : false;
                $advisore['image']        = $value->image ? url('uploads/image/' . $value->image) : url('uploads/placeholder/dummy_image.png');
                $advisore['screen_name']  = isset($value->screen_name) ? $value->screen_name : '';
                $advisore['service_name'] = isset($value->service_name) ? $value->service_name : '';
                $advisore['rating']       = isset($value->average_rating) ? $value->average_rating : '';
                $advisore['total_orders'] = Orders::where(['advisore_id' => $value->id])->count();
                $category_arrs            = [];
                $CustomerCategories       = CustomerCategories::where(['customer_id' => $value->id])->get();
                if (count($CustomerCategories) > 0) {
                    foreach ($CustomerCategories as $cus_cat_key => $cus_cat_value) {
                        //$get_categories[] = $cus_cat_value['caterogy_id'];
                        $get_category = Categories::where(['id' => $cus_cat_value['caterogy_id']])->first();
                        if ($get_category) {
                            $category  = array();
                            $category['id']    = $get_category->id;
                            $category['title'] = $get_category->title;
                            $category_arrs[]   = $category;
                        }
                    }
                }
                $advisore['categories'] = $category_arrs;

                /*$availability_arrs  = array();
                $CustomerAvailabilities = CustomerAvailabilities::where(['customer_id' => $value->id, 'status' => 'Active'])->get();
                foreach ($CustomerAvailabilities as $key => $value) {
                    $get_availability                = Availability::where(['id' => $value->availability_id])->first();
                    $availability                    = array();
                    $availability['id']              = $value->id;
                    $availability['availability_id'] = $value->availability_id;
                    $availability['title']           = $get_availability->title;
                    $availability['sub_title']       = $get_availability->sub_title;
                    $availability['image']           = $get_availability->image ? url('uploads/availability/' . $get_availability->image) : url('uploads/placeholder/dummy_image.png');
                    $availability['charges']         = $value->charges ? $value->charges : 0;
                    $availability_arrs[] = $availability;
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

                    $CustomerAvailabilities = CustomerAvailabilities::where(['availability_id' => $value1->id,'customer_id' => $value->id])->first();
                    if ($CustomerAvailabilities) {
                        $availability['id']              = $CustomerAvailabilities->id;
                        $availability['charges']         = $CustomerAvailabilities->charges ? $CustomerAvailabilities->charges : 0;
                        $availability['status']       = isset($CustomerAvailabilities->status) ? $CustomerAvailabilities->status : '';
                    }
                    
                    $availability_arrs[]        = $availability;
                }


                $advisore['availabilities'] = $availability_arrs;

                $advisore_arrs[] = $advisore;
            }
        }
        return $advisore_arrs;
    }

    /*public function add_wallet(Request $request){
        try {
            // Validate the request
            $err = [];
            $err['device_id']   = 'required';
            $err['amount']      = 'required|numeric';
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            try {
                $Auth                         = Auth::guard('api')->user();
                if (!isset($Auth->phone_number)) {
                    return response()->json(['status' => false, 'message' => "Please update your phone number"], 422);
                }
                if (!isset($Auth->date_of_birth)) {
                    return response()->json(['status' => false, 'message' => "Please update your birth date"], 422);
                }
                $CustomerWallet              = new CustomerWallet();
                $CustomerWallet->customer_id = $Auth->id;
                $CustomerWallet->amount      = $request->amount;
                $CustomerWallet->status      = 'PENDING';            
                $CustomerWallet->save();
                // return response()->json(['status' => true, 'message' => 'Add Wallet Successfully']);
                $responseData                    = $this->create_payment_link($request, $CustomerWallet->id);
                if(!isset($responseData->id)){
                    return response()->json(['status' => false, 'message' => 'Failed to create PayPal order'], 500);
                }
                if ($responseData->id) {
                    $getCustomerWallet               = CustomerWallet::where('id', $CustomerWallet->id)->first();
                    $getCustomerWallet->payment_json = \json_encode($responseData);
                    $getCustomerWallet->save();

                    $orderID                 = $responseData->url;
                    $data['paypal_order_id'] = $orderID;
                    $data['message']         = 'Create payment payPal order.';
                    return response()->json(['status' => true, 'message' => 'Add Wallet Successfully', 'data' => $data]);
                }
            } catch (TypeError $e) {
                return $e;
            } catch (Exception $e) {
                return $e;
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }*/

    public function add_wallet(PayPalService $paypal,Request $request){
        try {

            $Logs                           = new Logs();
            $Logs->title              = 'add_wallet api request';
            $Logs->data              = json_encode($request->all());
            $Logs->save();

            // Validate the request
            $err = [];
            $err['device_id']   = 'required';
            $err['amount']      = 'required|numeric';
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
                die();
            }

            try {

                $Auth                         = Auth::guard('api')->user();
                if (!isset($Auth->phone_number)) {
                    return response()->json(['status' => false, 'message' => "Please update your phone number"], 422);
                }
                if (!isset($Auth->date_of_birth)) {
                    return response()->json(['status' => false, 'message' => "Please update your birth date"], 422);
                }

                $payment_json = NULL;
                if (isset($request->payment_json)) {
                    $payment_json = $request->payment_json;
                }
                if ($request->paypal_token) {
                    $capture = $paypal->captureOrder($request->paypal_token);
                    $payment_json = json_encode($capture);
                    if (isset($capture['status'])) {
                        // code...
                    }else{
                        $Logs                           = new Logs();
                        $Logs->title              = 'add_wallet api';
                        $Logs->data              = json_encode($capture);
                        $Logs->save();
                        return response()->json(['status' => false, 'message' => 'Something went wrong.please try again.'], 422); die();
                    }
                }


                $CustomerWallet              = new CustomerWallet();
                $CustomerWallet->customer_id = $Auth->id;
                $CustomerWallet->amount      = $request->amount;
                if ($request->paypalResponse) {
                    //$getCustomerWallet->payment_json = json_encode($request->paypalResponse);
                }
                $CustomerWallet->payment_json = $payment_json;
                $CustomerWallet->status      = 'SUCCESS';            
                $CustomerWallet->save();
                
                $data = array(); 
                $data['name']  = $Auth->full_name;
                $data['email'] = $Auth->email;

                $data['subject']       = 'Your wallet has been recharged successfully.';
                $data['template']      = 'email.wallet_recharged';
                if ($data['template']) {
                    Mail::send($data['template'], $data, function ($message) use ($data) {
                        $message->to($data['email'], $data['name'])->subject($data['subject']);
                    });
                }

                $data = array(); 

                $data['message']         = 'Create payment payPal order.';
                return response()->json(['status' => true, 'message' => 'Add Wallet Successfully', 'data' => $data]);
            
            } catch (TypeError $e) {
                return $e;
            } catch (Exception $e) {
                return $e;
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
    }


}
