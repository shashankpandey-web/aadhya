<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        // Common data for the view
        $common = [
            'title' => 'Availabilities',
            'main_menu' => 'availabilities',
            'submain_menu' => 'availabilities',
            'filter_availability_name' => '',
            'filter_availability_status' => ''
        ];

        $availabilities = Availability::whereNull('is_delete')->orderBy('id', 'desc');
        $availabilities = $availabilities->paginate(config('adminconfig.records_per_page'));
        return view('admin.availability.index', compact('common', 'availabilities'));
    }

    public function store(Request $request, $id = '')
    {
        $common = array();

        /*$common = [
            'title' => 'Availabilities',
            'main_menu' => 'availabilities',
            'submain_menu' => 'availabilities',
        ];*/
        $common = [
            'title' => 'Availability Add/Edit',
            'main_menu' => 'availabilities',
            'submain_menu' => 'availability',
        ];
        $availability = null;

        // Check if ID is provided and valid
        if ($id != '') {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
            }
            $id = checkDecrypt($id);
            $availability = Availability::where('id', $id)->first();

            if (!$availability) {
                return back()->withErrors(['error' => translate('Something went wrong')]);
            }
            $common['id'] = encrypt($id);
        }

        // Handle POST request
        if ($request->isMethod('post')) {
            $req_fields = array();

            $req_fields = [
                'title' => 'required',
                'status' => 'required|in:Active,Deactive',
            ];

            $errormsg = [
                'title' => translate('Title'),
                'status' => translate('Status'),
            ];

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                    'in' => 'The selected :attribute is invalid.',
                ],
                $errormsg
            );

            if ($validation->fails()) {
                $validation->errors()->add('error', 'Something is wrong with the required fields!');
                return back()->withErrors($validation)->withInput();
            }


            if ($request->id != '') {      
                $message = translate('Update Successfully');
                $status = 'success';
                $availability = Availability::find($id);
            }else {
                $message = translate('Add Successfully');
                $status = 'success';
                $availability = new Availability();
            }


            if ($request->hasFile('image')) {
                $path = 'uploads/availability';
                if ($id && $availability && $availability->image) {
                    $imagePath = image_upload($request->file('image'), $path, $availability->image);
                } else {
                    $imagePath = image_upload($request->file('image'), $path);
                }
                $availability->image = $imagePath;
            }

            $availability->title = $request->title;
            $availability->sub_title = $request->sub_title;
            $availability->status = $request->status;
            $availability->save();

            return redirect()->route('admin.availabilities')->withErrors([$status => $message]);
        }
        return view('admin.availability.store', compact('common', 'availability'));
    }

    public function delete($id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
        }
        $id = checkDecrypt($id);
        $status = 'error';
        $message = translate('Something went wrong!');
        $get_availability = Availability::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_availability) {
            $get_availability->is_delete = 1;
            $get_availability->save();
            $status = 'success';
            $message = translate('Delete Successfully');
        }
        return back()->withErrors([$status => $message]);
    }
}
