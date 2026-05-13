<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\Customergroups;
use App\Models\Countries;
use App\Models\States;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Mail;


class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $common = array();
        $common['title'] = 'Customers';
        $common['main_menu'] = 'customers';
        $common['submain_menu'] = 'customers';
        $common['filter_title'] = '';
        $common['filter_status'] = '';


        //if ($request->isMethod('post')) {
            $filterFields = ['filter_title', 'filter_status'];
            $common = updateSessionFilters($request, $filterFields, $common);
        //}

        $customers = Customers::whereNull('is_delete')->whereNull('step')->orderBy('id', 'desc');

        if ($common['filter_title']) {
            $customers->where('full_name', 'like', '%' . $common['filter_title'] . '%');
        }

        $customers->where('type', 'Customer');

        if ($common['filter_status']) {
            $customers->where('status', $common['filter_status']);
        }

        $customers = $customers->paginate(config('adminconfig.records_per_page'));
        return view('admin.customer.index', compact('common', 'customers'));
    }

    public function view(Request $request, $id = '')
    {
        $common = array();
        $common = [
            'title' => 'Customer',
            'main_menu' => 'customers',
            'submain_menu' => 'customers',
        ];

        if ($id != '') {
            $get_customer = Customers::where('id', checkDecrypt($id))->first();
            if ($get_customer) {

                $get_country = Countries::where(['id' => $get_customer->country])->first();
                if ($get_country) {
                    $get_customer['country'] = $get_country->name;
                }

                $get_state = States::where(['id' => $get_customer->state])->first();
                if ($get_state) {
                    $get_customer['state'] = $get_state->name;
                }

                $get_city = City::where(['id' => $get_customer->city])->first();
                if ($get_city) {
                    $get_customer['city'] = $get_city->name;
                }

                return view('admin.customer.view', compact('common', 'get_customer'));
            } else {
                $status = 'success';
                $message = translate('Something went wrong.');
                return back()->withErrors([$status => $message]);
            }
        } else {
            $status = 'success';
            $message = translate('Something went wrong.');
            return back()->withErrors([$status => $message]);
        }
    }

    public function delete($id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
        }
        $id = checkDecrypt($id);
        $status = 'error';
        $message = translate('Something went wrong!');
        $get_customer = Customers::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_customer) {
            $get_customer->is_delete = 1;
            $get_customer->save();
        }
        $status = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }


    public function change_status(Request $request)
    {
        $status = 'error';
        $message = translate('Something went wrong!');
        if (isset($request->id)) {
            $id = $request->id;
            $get_customer = Customers::where(['id' => $id])->whereNull('is_delete')->first();
            if ($get_customer) {
                $get_customer->status = $get_customer->status == 'Active' ? 'Inactive' : 'Active';
                $get_customer->save();
                $status  = $get_customer->status;
                $message = translate('Status Change Successfully');

                $data['email']    = $get_customer->email;
                $data['name']     = $get_customer->full_name;
                $data['subject']  = '';
                $data['template'] = '';

                if ($status == 'Inactive') {
                    $data['subject']  = 'Account Inactive: Login Access Restricted';
                    $data['template'] = 'email.advidor_inactive';
                }

                if ($status == 'Active') {
                    $data['subject'] = 'Account Activated: You Can Now Log In';
                    $data['template'] = 'email.advidor_active';
                }
                if ($data['template']) {
                    Mail::send($data['template'], $data, function ($message) use ($data) {
                        $message->to($data['email'], $data['name'])
                            ->subject($data['subject']);
                    });
                }
            }
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
        ], 200);
    }

    public function customergroups(Request $request)
    {
        $common = array();
        $common['title'] = 'Customer groups';
        $common['main_menu'] = 'customers';
        $common['submain_menu'] = 'customergroup';
        $common['filter_customergroup_title'] = '';
        $common['filter_customergroup_status'] = '';

        if ($request->isMethod('post')) {
            $filterFields = ['filter_customergroup_title', 'filter_customergroup_status'];
            $common = updateSessionFilters($request, $filterFields, $common);
        }

        $customergroups = Customergroups::where(['type' => 'Customer'])->whereNull('is_delete')->orderBy('id', 'desc');

        if ($common['filter_customergroup_title']) {
            $customergroups->where('title', 'like', '%' . $common['filter_customergroup_title'] . '%');
        }

        if ($common['filter_customergroup_status']) {
            $customergroups->where('status', $common['filter_customergroup_status']);
        }

        $customergroups = $customergroups->paginate(config('adminconfig.records_per_page'));

        return view('admin.customergroup.index', compact('common', 'customergroups'));
    }


    public function customergroups_store(Request $request, $id = '')
    {
        $common = array();
        $common = [
            'title' => 'Customer group',
            'main_menu' => 'customers',
            'submain_menu' => 'customergroup',
        ];

        $get_customergroup = getTableColumn('customer_groups');
        
        if ($id != '') {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
            }
            $id = checkDecrypt($id);
            $get_customergroup = Customergroups::where('id', $id)->first();
            if (!$get_customergroup) {
                return back()->withErrors(['error' => translate('Something went wrong')]);
            }
        }

        if ($request->isMethod('post')) {
            $req_fields = array();
            $req_fields['title'] = 'required';
            $req_fields['customer_id']   = "required";

            $errormsg = [
                'title' => translate('Title'),
                'customer_id' => translate('Customer'),
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
            if ($request->id != '') {
                $message = translate('Update Successfully');
                $status = 'success';
                $Customergroup = Customergroups::find($request->id);
            } else {
                $message = translate('Add Successfully');
                $status = 'success';
                $Customergroup = new Customergroups();
            }


            $Customergroup->title = $request->title;
            $Customergroup->status = $request->status;
            if ($request->customer_id) {
                $Customergroup->customer_id = json_encode($request->customer_id);
            }else{
                $Customergroup->customer_id = NULL;
            }
            $Customergroup->type = 'Customer';
            $Customergroup->save();

            return redirect()->route('admin.customergroups')->withErrors([$status => $message]);
        }

        $get_customers = Customers::where(['type' => 'Customer','status' => 'Active'])->whereNull('is_delete')->orderBy('id', 'desc')->get();
        return view('admin.customergroup.store', compact('common', 'get_customergroup', 'get_customers'));
    }

    public function customergroups_delete($id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
        }
        $id = checkDecrypt($id);
        $status = 'error';
        $message = translate('Something went wrong!');
        $get_customergroup = Customergroups::where(['id' => $id,'type' => 'Customer'])->whereNull('is_delete')->first();
        if ($get_customergroup) {
            $get_customergroup->is_delete = 1;
            $get_customergroup->save();
        }
        $status = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }
}
