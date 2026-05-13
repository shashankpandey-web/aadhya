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
use App\Models\Categories;
use App\Models\Customerwishlist;
use App\Models\Advisorreviews;
use App\Models\CustomerCategories;
use App\Models\Orders;
use App\Models\Availability;
use App\Models\CustomerAvailabilities;


class WishlistController extends Controller
{

    public function add_to_wishlist(Request $request){

        try {
            
            // Validate the request
            $err = [
                'device_id'     => 'required',
                'advisore_id'   => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false,'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth = Auth::guard('api')->user();
            $advisore = Customers::where(['id' => $request->advisore_id ,'status' => 'Active', 'type' => 'Advisor' ])->whereNull('is_delete')->first();
            if( $advisore ){
                $customer_wishlist = Customerwishlist::where(['customer_id' => $Auth->id ,'advisore_id' => $request->advisore_id ]) ->first();
                if(!$customer_wishlist){
                    $Wishlist = new Customerwishlist();
                    $Wishlist->customer_id = $Auth->id;
                    $Wishlist->advisore_id = $request->advisore_id;
                    $added_wishlist = $Wishlist->save();
                    if($added_wishlist){
                        return response()->json(['status' => true,'message' => 'Added to Favorite']);
                    }
                }else{
                    return response()->json(['status' => true,'message' => 'Already Added to Favorite']);
                }
            }else{
                return response()->json(['status' => false,'message' => 'Something went wrong'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false,'message' => 'Unauthorized'], 401);
        }
    }

    public function remove_to_wishlist(Request $request){
        try {
            // Validate the request
            $err = [
                'device_id'     => 'required',
                'advisore_id'   => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false,'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth = Auth::guard('api')->user();
            $advisore = Customers::where(['id' => $request->advisore_id ,'status' => 'Active', 'type' => 'Advisor' ])->whereNull('is_delete')->first();
            if( $advisore ){
                $customer_wishlist = Customerwishlist::where(['customer_id' => $Auth->id ,'advisore_id' => $request->advisore_id ]) ->first();
                if($customer_wishlist){
                    $remove_wishlist = Customerwishlist::where(['id' => $customer_wishlist->id ])->delete();
                    if( $remove_wishlist ){
                        return response()->json(['status' => true,'message' => 'Remove from Favorite']);
                    }
                }else{
                    return response()->json(['status' => false,'message' => 'Something went wrong'], 422);
                }
            }else{
                return response()->json(['status' => false,'message' => 'Something went wrong'], 422);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false,'message' => 'Unauthorized'], 401);
        }
    }

    public function favorite_advisores(Request $request){
        try {
            // Validate the request
            $err = [
                'device_id'   => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false,'message' => $validation->errors()->first()], 422);
                die();
            }

            $Auth = Auth::guard('api')->user();
            /*$get_wishlists = Customerwishlist::orderBy('id', 'desc')->where(['customer_id' => $Auth->id ])->get();
            $favorite_advisores = array();
            foreach ($get_wishlists as $key => $value) {
                $favorite_advisore = [];
                $favorite_advisore['id'] = $value->id;
                $get_advisore = Customers::where(['id' => $value->advisore_id ,'status' => 'Active', 'type' => 'Advisor' ])->whereNull('is_delete')->first();
                if( $get_advisore ){
                    $favorite_advisore['advisore_id']    = $get_advisore->id;
                    $favorite_advisore['is_wishlist']    = true;
                    $favorite_advisore['full_name']      = isset($get_advisore->full_name) ? $get_advisore->full_name : '';
                    $favorite_advisore['is_online']      = ($get_advisore->is_online==1) ? true : false;
                    $favorite_advisore['is_busy']        = ($get_advisore->is_busy==1) ? true : false;
                    $favorite_advisore['image']          = $get_advisore->image ? url('uploads/image/'.$get_advisore->image) : url('uploads/placeholder/dummy_image.png');
                    $favorite_advisore['screen_name']    = isset($get_advisore->screen_name) ? $get_advisore->screen_name : '';
                    $favorite_advisore['service_name']   = isset($get_advisore->service_name) ? $get_advisore->service_name : '';
                    $favorite_advisore['about_my_service']       = isset($get_advisore->about_my_service) ? $get_advisore->about_my_service : '';
                    $favorite_advisore['about_me']               = isset($get_advisore->about_me) ? $get_advisore->about_me : '';
                    $favorite_advisore['ordering_instructions']  = isset($get_advisore->ordering_instructions) ? $get_advisore->ordering_instructions : '';
                    $favorite_advisore['my_video']               = $get_advisore->my_video ? get_image_upload_s3($get_advisore->my_video) : '';  

                    $customer_category_arr = [];
                    $get_customer_categories = CustomerCategories::where(['customer_id' => $get_advisore->id])->get();
                    if ($get_customer_categories) {
                        foreach ($get_customer_categories as $key => $value) {
                            $get_customer_category = Categories::where(['id' => $value->caterogy_id])->first();
                            if($get_customer_category){
                                if(isset($get_customer_category->title)){
                                    $customer_category_arr[] = $get_customer_category->title;
                                }
                            }
                        }
                    }

                    $availability_arrs  = array();
                    $get_availability = Availability::where(['status' => 'Active'])->whereNull('is_delete')->orderBy('id', 'desc')->get();
                    foreach ($get_availability as $key => $value1) {
                        $availability                    = array();
                        $availability['availability_id'] = $value1->id;
                        $availability['title']           = isset($value1->title) ? $value1->title : '';
                        $availability['sub_title']       = isset($value1->sub_title) ? $value1->sub_title : '';
                        $availability['image']           = $value1->image ? url('uploads/availability/'.$value1->image) : url('uploads/placeholder/dummy_image.png');

                        $availability['charges']         = 0;
                        $availability['status']       = 'Deactive';

                        $CustomerAvailabilities = CustomerAvailabilities::where(['availability_id' => $value1->id,'customer_id' => $get_advisore->id])->first();
                        if ($CustomerAvailabilities) {
                            $availability['charges']         = $CustomerAvailabilities->charges ? $CustomerAvailabilities->charges : 0;
                            $availability['status']       = isset($CustomerAvailabilities->status) ? $CustomerAvailabilities->status : '';
                        }
                        
                        $availability_arrs[]        = $availability;
                    }                
                    $favorite_advisore['availabilities'] = $availability_arrs;
                    $favorite_advisore['category']               = implode(', ', $customer_category_arr);
                    $favorite_advisore['rating']                 = isset($get_advisore->average_rating) ? $get_advisore->average_rating : '';
                    $favorite_advisore['total_orders']           = Orders::where(['advisore_id' => $request->advisore_id])->count();
                    $favorite_advisore['year_joined']            = date('Y', strtotime($get_advisore->created_at));
                    $favorite_advisores[] = $favorite_advisore;
                }

            }*/

            $setLimit     = config('apiconfig.records_per_page');
            $offset       = 0;
            if ($request->offset) {
                $offset   = $request->offset-1;
            }
            $limit        = $offset * $setLimit;


            $get_wishlists_new = Customerwishlist::query()
                ->select(
                    'wishlist.*',
                    'cc.full_name',
                    'cc.is_online',
                    'cc.is_busy',
                    'cc.image',
                    'cc.screen_name',
                    'cc.service_name',
                    'cc.about_my_service',
                    'cc.about_me',
                    'cc.ordering_instructions',
                    'cc.my_video',
                    'cc.average_rating',
                    'cc.created_at'
                )
                ->where('wishlist.customer_id', $Auth->id)
                ->rightJoin('customers as cc', function ($join) {
                    $join->on('cc.id', '=', 'wishlist.advisore_id')
                         ->where('cc.type', 'Advisor')
                         ->where('cc.status', 'Active')
                         ->whereNull('cc.is_delete');
                });


            $wishlists_count = $get_wishlists_new->count();
            $get_wishlists_new = $get_wishlists_new->orderBy('id', 'desc')->offset($limit)->take($setLimit)->get();
            $favorite_advisores_new = array();
            foreach ($get_wishlists_new as $key => $value) {
                $favorite_advisore = [];
                $favorite_advisore['id']             = $value->id;
                $favorite_advisore['advisore_id']    = $value->advisore_id;
                $favorite_advisore['is_wishlist']    = true;
                $favorite_advisore['full_name']      = isset($value->full_name) ? $value->full_name : '';
                $favorite_advisore['is_online']      = ($value->is_online==1) ? true : false;
                $favorite_advisore['is_busy']        = ($value->is_busy==1) ? true : false;
                $favorite_advisore['image']          = $value->image ? url('uploads/image/'.$value->image) : url('uploads/placeholder/dummy_image.png');
                $favorite_advisore['screen_name']    = isset($value->screen_name) ? $value->screen_name : '';
                $favorite_advisore['service_name']   = isset($value->service_name) ? $value->service_name : '';
                $favorite_advisore['about_my_service']       = isset($value->about_my_service) ? $value->about_my_service : '';
                $favorite_advisore['about_me']               = isset($value->about_me) ? $value->about_me : '';
                $favorite_advisore['ordering_instructions']  = isset($value->ordering_instructions) ? $value->ordering_instructions : '';
                $favorite_advisore['my_video']               = $value->my_video ? get_image_upload_s3($value->my_video) : ''; 

                $customer_category_arr = [];
                $get_customer_categories = CustomerCategories::where(['customer_id' => $value->advisore_id])->get();
                if ($get_customer_categories) {
                    foreach ($get_customer_categories as $key => $value) {
                        $get_customer_category = Categories::where(['id' => $value->caterogy_id])->first();
                        if($get_customer_category){
                            if(isset($get_customer_category->title)){
                                $customer_category_arr[] = $get_customer_category->title;
                            }
                        }
                    }
                }

                $availability_arrs  = array();
                $get_availability = Availability::where(['status' => 'Active'])->whereNull('is_delete')->orderBy('id', 'desc')->get();
                foreach ($get_availability as $key => $value1) {
                    $availability                    = array();
                    $availability['availability_id'] = $value1->id;
                    $availability['title']           = isset($value1->title) ? $value1->title : '';
                    $availability['sub_title']       = isset($value1->sub_title) ? $value1->sub_title : '';
                    $availability['image']           = $value1->image ? url('uploads/availability/'.$value1->image) : url('uploads/placeholder/dummy_image.png');

                    $availability['charges']         = 0;
                    $availability['status']       = 'Deactive';

                    $CustomerAvailabilities = CustomerAvailabilities::where(['availability_id' => $value1->id,'customer_id' => $value->customer_id])->first();
                    if ($CustomerAvailabilities) {
                        $availability['charges']         = $CustomerAvailabilities->charges ? $CustomerAvailabilities->charges : 0;
                        $availability['status']       = isset($CustomerAvailabilities->status) ? $CustomerAvailabilities->status : '';
                    }
                    
                    $availability_arrs[]        = $availability;
                }                
                $favorite_advisore['availabilities'] = $availability_arrs;

                $favorite_advisore['category']               = implode(', ', $customer_category_arr);
                $favorite_advisore['rating']                 = isset($value->average_rating) ? $value->average_rating : '';
                $favorite_advisore['total_orders']           = Orders::where(['advisore_id' => $request->advisore_id])->count();
                $favorite_advisore['year_joined']            = date('Y', strtotime($value->created_at));
                $favorite_advisores_new[] = $favorite_advisore;
            }
            return response()->json(['status' => true,'message' => 'Favorite Advisore list', 'data' => $favorite_advisores_new, 'page_count' => ceil($wishlists_count / $setLimit), 'total_count' => $wishlists_count ]);
        } catch (Exception $e) {
            return response()->json(['status' => false,'message' => 'Unauthorized'], 401);
        }
    }

}