<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

use App\Models\Settings;

class SettingController
{
    //
    public function settings(Request $request)
    {
        $common                 = array();
        $common['title']        = "Settings";
        $common['main_menu']    = "Settings";
        $common['submain_menu'] = "Settings";
        $common['button']       = "Update";

        $get_setting = Settings::where('status', 'active')->orderby('sort', 'Asc')->get();
        foreach ($get_setting as $key => $value) {
            $get_settings[$value['meta_title']] = $value['content'];
        }

        // print_die($get_settings);

        if ($request->isMethod('post')) {

            // print_die($request->all());
            $req_fields                 = array();
            // $req_fields['header_logo']  = "image|mimes:jpg,jpeg,png,gif";
            // $req_fields['favicon']      = "image|mimes:jpg,jpeg,png,gif";
            // $req_fields['footer_logo']  = "image|mimes:jpg,jpeg,png,gif";



            // $errormsg = [
            //     // "header_logo"      => "Header Logo",
            //     // "favicon"          => "Favicon",
            //     // "footer_logo"      => "Footer Logo",
            // ];

            // $validation = Validator::make(
            //     $request->all(),
            //     $req_fields,
            //     [
            //         'required' => 'The :attribute field is required.',
            //     ],
            //     $errormsg
            // );

            // if ($validation->fails()) {
            //     return back()->withErrors($validation)->withInput();
            // }
            $rq_data = array();
            $rq_data = $request->except('_token');

            if ($request->tax_status) {
                $Settings         =  Settings::where('meta_title', 'tax')->first();
                $Settings->column_status = $request->tax_status;
                $Settings->save();
            }
            foreach ($rq_data as $key => $value) {
                $Settings =  Settings::where('meta_title', $key)->first();
                if ($Settings) {
                    if ($key == 'header_logo') {
                        if ($request->hasFile($key)) {
                            $random_no  = uniqid();
                            $img = $request->file($key);
                            $ext = $img->getClientOriginalExtension();
                            $new_name = $random_no . '.' . $ext;
                            $destinationPath =  public_path('assets/uploads/setting');
                            $img->move($destinationPath, $new_name);
                            $Settings->content = $new_name;
                        }
                    } else {
                        $Settings->content = $value;
                    }
                    $Settings->save();
                }
            }
            return back()->withErrors(["success" => "Update Successfully"]);
        }
        return view('admin.settings.setting', compact('common', 'get_settings'));
    }
}
