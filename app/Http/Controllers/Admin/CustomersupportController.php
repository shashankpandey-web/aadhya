<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\Customersupport;
use App\Models\Customersupportimage;
use App\Models\Customersupportlog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CustomersupportController extends Controller
{
    public function index(Request $request)
    {
        $common = array();
        $common['title'] = 'Customer Support';
        $common['main_menu'] = 'customersupport';
        $common['submain_menu'] = 'customersupport';


        $common['filter_customersupport_customer_id'] = '';
        $common['filter_customersupport_status'] = '';

        if ($request->isMethod('post')) {
            $filterFields = ['filter_customersupport_customer_id', 'filter_customersupport_status'];
            $common = updateSessionFilters($request, $filterFields, $common);
        }

        $customersupports = Customersupport::whereNull('is_delete')->orderBy('id', 'desc');

        if ($common['filter_customersupport_customer_id']) {
            $customersupports->where('customer_id', $common['filter_customersupport_customer_id']);
        }

        if ($common['filter_customersupport_status']) {
            $customersupports->where('status', $common['filter_customersupport_status']);
        }

        $customersupports = $customersupports->paginate(config('adminconfig.records_per_page'));

        $customer_supports = array();

        foreach ($customersupports as $key => $value) {
            $value->customer_name = '';
            $Customers = Customers::where(['id' => $value->customer_id])->first();
            if ($Customers) {
                $value->customer_name = $Customers->full_name;
            }
            $customer_supports = $value;
        }

        $customers = Customers::whereNull('is_delete')->whereNull('step')->orderBy('id', 'desc')->get();

        return view('admin.customersupport.index', compact('common', 'customersupports','customers'));
    }

    public function view(Request $request, $id = '')
    {
        $common = array();
        $common = [
            'title' => 'Customer Support',
            'main_menu' => 'customersupport',
            'submain_menu' => 'customersupport',
        ];

        
        if ($id == '') {
            return back()->withErrors(['error' => translate('Something went wrong')]);
        }

        if ($id != '') {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
            }
            $id = checkDecrypt($id);
            $get_customersupport = Customersupport::where('id', $id)->first();
            if (!$get_customersupport) {
                return back()->withErrors(['error' => translate('Something went wrong')]);
            }
        }
        $Customersupportimage = Customersupportimage::where(['customer_support_id' => $get_customersupport->id])->get();

        $Customers = Customers::where(['id' => $get_customersupport->customer_id])->first();
        $To_Customers = Customers::where(['id' => $get_customersupport->to_customer_id])->first();
        
        return view('admin.customersupport.view', compact('common', 'get_customersupport','Customersupportimage','Customers','To_Customers'));
    }

    public function update(Request $request)
    {
        $common = array();
        $common = [
            'title' => 'Customer Support',
            'main_menu' => 'customersupport',
            'submain_menu' => 'customersupport',
        ];

        /*echo "<pre>";
        print_r($request->all());
        echo "</pre>";
        die;*/

        
        if ($request->id == '' && $request->status == '') {
            return back()->withErrors(['error' => translate('Something went wrong')]);
        }

        $id = $request->id;

        if ($id != '') {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
            }
            $id = checkDecrypt($id);
            $get_customersupport = Customersupport::where('id', $id)->first();
            if (!$get_customersupport) {
                return back()->withErrors(['error' => translate('Something went wrong')]);
            }
            $Customersupportlog = new Customersupportlog();
            $Customersupportlog->prev_status = $get_customersupport->status;
            $Customersupportlog->status = $request->status;
            $Customersupportlog->save();

            $get_customersupport->status = $request->status;
            $get_customersupport->save();
            return back()->withErrors(['success' => translate('Update Successfully')]);
        }
        return back()->withErrors(['error' => translate('Something went wrong')]);
    }
}
