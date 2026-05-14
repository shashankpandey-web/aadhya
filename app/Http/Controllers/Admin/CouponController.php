<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\Coupon;

class CouponController extends Controller
{

    // Coupon List
    public function index()
    {  
        $common = array();
        $common['title'] = 'Coupon';
        $common['main_menu']    = 'coupon';
        $common['submain_menu'] = 'coupon';
        $getCoupon = Coupon::whereNull('is_delete')->orderBy('id', 'desc')->paginate(config('adminconfig.records_per_page'));
        
        return view('admin.coupon.index', compact('common', 'getCoupon'));
    }

    // Add Or Update Coupon
    public function store(Request $request, $id = ""){

        $common = array();
        $common['title']        = 'Coupon Add';
        $common['main_menu']    = 'coupon';
        $common['submain_menu'] = 'coupon';
        
        $CouponID = "";
        if ($id != "") {          
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
         
            $id  = checkDecrypt($id);
            $Coupon = Coupon::where('id', $id)->first();
            $message    = translate("Update Successfully");
            $status     = "success";
            if (!$Coupon) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
            $CouponID = encrypt($id);
        }else{
            $Coupon = new Coupon();
            $message    = translate("Add Successfully");
            $status     = "success";
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['title']   = "required";
            $req_fields['code']    = "required";
            $req_fields['value']   = "required";

            $errormsg = [
                "title" => translate("Title"),
                "code" => translate("Code"),
                "value" => translate("Value"),
            ];

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                ],
                $errormsg
            );

            if ($validation->fails()) {
                $validation->errors()->add('error', 'Something is wrong with required field!');
                return back()->withErrors($validation)->withInput();
            }
            $Coupon->title          = $request->title;
            $Coupon->code           = $request->code;
            $Coupon->value          = $request->value;
            $Coupon->usage_limit_per_user   = $request->usage_limit_per_user;
            $Coupon->usage_limit_per_coupon = $request->usage_limit_per_coupon;
            $Coupon->maximum_amount = $request->maximum_amount;
            $Coupon->start_date     = isset($request->start_date) ? $request->start_date.' 00:00:00' : NULL;
            $Coupon->expiry_date    = isset($request->expiry_date) ? $request->expiry_date.' 23:59:59' : NULL;
            $Coupon->status         = $request->status;
            $Coupon->type               = $request->type ?? 'flat';
            $Coupon->is_comeback_offer  = $request->has('is_comeback_offer') ? 1 : 0;
            $Coupon->is_birthday_offer  = $request->has('is_birthday_offer') ? 1 : 0;
            $Coupon->save();

            // Enforce single active flag — clear the flag on all other coupons
            if ($Coupon->is_comeback_offer) {
                Coupon::where('id', '!=', $Coupon->id)->update(['is_comeback_offer' => 0]);
            }
            if ($Coupon->is_birthday_offer) {
                Coupon::where('id', '!=', $Coupon->id)->update(['is_birthday_offer' => 0]);
            }
            return redirect()->route('admin.coupon')->withErrors([$status => $message]);
        }
      
        return view('admin.coupon.store', compact('common','Coupon','CouponID'));
    }

    // Delete Coupon
    public function delete($id){
        
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $status       = 'error';
        $message      = translate('Something went wrong!');
        $Coupon =  Coupon::where(['id' => $id])->whereNull('is_delete')->first();
      
        if ($Coupon) {
            $Coupon->is_delete = 1;
            $Coupon->save();
            $status  = 'success';
            $message = translate('Delete Successfully');
        }

        return back()->withErrors([$status => $message]);
    }

   
}
