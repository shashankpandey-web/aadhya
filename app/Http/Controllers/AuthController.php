<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Customers;
use App\Models\TrackingPass;
use App\Models\User;
use App\Models\Orders;
use Carbon\Carbon;
use Exception;
use Mail;
use PDF;
use View;
use Dompdf\Dompdf;

class AuthController extends Controller
{

    public function login1(Request $request){
        try {

            if ($request->isMethod('post')) {
            }else{
                return response()->json(['status' => false,'message' => 'Method Not Allowed. Please use POST for this API.'], 405);
            }

            // Validate the request
            $err = [
                'email' => 'required',
                'password' => 'required',
                'device_id' => 'required',
            ];
            $validation = Validator::make($request->all(), $err);
            if ($validation->fails()) {
                return response()->json(['status' => false,'message' => $validation->errors()->first()], 422);
                die();
            }

            // Find customer by email
            $customer = Customers::where('email', $request->email)->where('status', 'Active')->whereNull('is_delete')->first();
            if (!$customer) {
                return response()->json(['status' => false,'message' => 'Unauthorized'], 401);
            }

            if (Hash::check($request->password, $customer->password)) {
                $token = Auth::guard('api')->login($customer);
                return response()->json([
                    'status' => true,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
                    'message' => 'Login Successfully'
                ]);
            } else {
                return response()->json(['status' => false,'message' => 'Unauthorized'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false,'message' => 'Unauthorized'], 401);
        }
    }

    // Get authenticated customer
    public function profile1(Request $request)
    {   
        try {
            if ($request->isMethod('post')) {
            }else{
                return response()->json(['status' => false,'error' => 'Method Not Allowed. Please use POST for this API.'], 405);
            }
            $Auth = Auth::guard('api')->user();
            if ($Auth) {
                $Customers = Customers::where('id', $Auth->id)->where('status', 'Active')->whereNull('is_delete')->first();
                if ($Customers) {
                    $data = [];
                    return response()->json(['status' => true,'message' => 'Profile','data' => $data]);
                }
            }
            return response()->json(['status' => false,'error' => 'Unauthorized'], 401);
        } catch (Exception $e) {
            return response()->json(['status' => false,'error' => 'Unauthorized'], 401);
        }
    }
}
