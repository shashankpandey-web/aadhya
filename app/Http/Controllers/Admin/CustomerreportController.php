<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\Customerreport;
use App\Models\Customerreportimage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CustomerreportController extends Controller
{
    public function index(Request $request)
    {
        $common = array();
        $common['title'] = 'Customer Report';
        $common['main_menu'] = 'customerreport';
        $common['submain_menu'] = 'customerreport';

        $common['filter_customerreport_customer_id'] = '';
        $common['filter_customerreport_to_customer_id'] = '';

        if ($request->isMethod('post')) {
            $filterFields = ['filter_customerreport_customer_id','filter_customerreport_to_customer_id'];
            $common = updateSessionFilters($request, $filterFields, $common);
        }

        $customerreports = Customerreport::whereNull('is_delete')->orderBy('id', 'desc');

        if ($common['filter_customerreport_customer_id']) {
            $customerreports->where('customer_id', $common['filter_customerreport_customer_id']);
        }
        if ($common['filter_customerreport_to_customer_id']) {
            $customerreports->where('to_customer_id', $common['filter_customerreport_to_customer_id']);
        }

        $customerreports = $customerreports->paginate(config('adminconfig.records_per_page'));

        $customer_reports = array();

        foreach ($customerreports as $key => $value) {
            $value->customer_name = '';
            $Customers = Customers::where(['id' => $value->customer_id])->first();
            if ($Customers) {
                $value->customer_name = $Customers->full_name;
            }
            $value->to_customer_name = '';
            $To_Customers = Customers::where(['id' => $value->to_customer_id])->first();
            if ($To_Customers) {
                $value->to_customer_name = $To_Customers->full_name;
            }
            $customer_reports = $value;
        }

        $customers = Customers::whereNull('is_delete')->whereNull('step')->orderBy('id', 'desc')->get();

        return view('admin.customerreport.index', compact('common', 'customerreports','customers'));
    }

    public function view(Request $request, $id = '')
    {
        $common = array();
        $common = [
            'title' => 'Customer Report',
            'main_menu' => 'customerreport',
            'submain_menu' => 'customerreport',
        ];

        
        if ($id == '') {
            return back()->withErrors(['error' => translate('Something went wrong')]);
        }

        if ($id != '') {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
            }
            $id = checkDecrypt($id);
            $get_customerreport = Customerreport::where('id', $id)->first();
            if (!$get_customerreport) {
                return back()->withErrors(['error' => translate('Something went wrong')]);
            }
        }
        $Customerreportimage = Customerreportimage::where(['customer_report_id' => $get_customerreport->id])->get();

        $Customers = Customers::where(['id' => $get_customerreport->customer_id])->first();
        $To_Customers = Customers::where(['id' => $get_customerreport->to_customer_id])->first();
        
        return view('admin.customerreport.view', compact('common', 'get_customerreport','Customerreportimage','Customers','To_Customers'));
    }
}
