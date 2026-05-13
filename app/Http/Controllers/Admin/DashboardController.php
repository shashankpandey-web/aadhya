<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Languages;
use App\Models\Countries;
use App\Models\States;
use App\Models\City;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Session;
use View;
use Mail;

class DashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        $common = array();
        $common['title'] = 'Dashboard';
        $common['main_menu']    = 'dashboard';
        $common['submain_menu'] = 'dashboard';

        return view('admin.dashboard', compact('common'));
    }



    //User Profile
    public function user_profile(Request $request){
        $id =  auth()->user()->id;
        $get_user = User::where(['id' => $id])->first();
        if ($request->isMethod('post')) {
            $data = $request->all();

            $req_fields = array();
            $errormsg   = array();
            $req_fields['name'] = "required";
            $errormsg['name'] = "Name";

            if ($request->has('is_change_password')) {
                $req_fields['old_password'] = "required";
                $req_fields['new_password'] = 'min:6|required';
                $req_fields['password_confirmation'] = "min:6|same:new_password";

                $errormsg['old_password'] = "Old Password";
                $errormsg['new_password'] = "New Password";
                $errormsg['password_confirmation'] = "Confirm Password";
            }

            $validator = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                    'same' => 'The :attribute confirmation does not match.',
                    'min' => 'The :attribute must have atleast 6 charectors.'
                ],
                $errormsg
            );

            if ($validator->fails()) {
                return redirect(route('admin.profile'))->withErrors($validator)->withInput();
            }

            if ($request->has('is_change_password') && $request->is_change_password != "") {
                if (Hash::check($request->old_password, $get_user->password)) {
                } else {
                    return redirect(route('admin.profile'))->withErrors(["error" => "Incorrect old password...."]);
                }
            }

            $get_user->name      = $request->name;
            if ($request->hasFile('profile_pic')) {
                //$img = $request->file('profile_pic');
                $path = 'uploads/users';
                $imagePath = image_upload($request->file('profile_pic'), $path);
                $get_user->image = $imagePath;
            }

            if ($request->has('is_change_password') && $request->is_change_password != "") {
                $get_user->password = Hash::make($request->new_password);

                $name          = $get_user->name;
                $email         = $get_user->email;
                $data = array('subject' => "Change Password",'email' => $email, 'name' => $name, 'page' => 'email.changepassword');

                Mail::send($data['page'], $data, function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['subject']);
                });
            }
            $get_user->save();
            return redirect(route('admin.profile'))->withErrors(["success" => "Propfile updated successfully...."]);
        }

        $common = array();
        $common['title'] = 'Dashboard';
        $common['main_menu']    = 'profile';
        $common['submain_menu'] = 'profile';

        return view('admin.profile', compact('common', 'get_user'));
    }


    ///Logout
    public function logout(){
        session()->flush();
        return redirect()->route('admin.login');
    }
}
