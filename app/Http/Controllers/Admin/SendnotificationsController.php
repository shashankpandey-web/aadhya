<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Sendnotifications;
use App\Models\Customers;
use App\Models\Customergroups;
use App\Models\Coupon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Session;
use View;
use Mail;
class SendnotificationsController extends Controller
{

    public function index(Request $request)
    {
        $common = [];
        $common['title'] = 'Send Notifications';
        $common['main_menu'] = 'sendnotification';
        $common['submain_menu'] = 'sendnotification';
        $get_sendnotifications = Sendnotifications::orderBy('id', 'desc')->whereNull('is_delete')->paginate(config('adminconfig.records_per_page'));
        return view('admin.sendnotification.index', compact('common', 'get_sendnotifications'));
    }

    public function add(Request $request)
    {
        try {
            $common = [
                'title' => 'Send Notifications',
                'heading_title' => 'Add Notification',
                'button' => translate('Save'),
            ];
            $common['main_menu'] = 'sendnotification';
            $common['submain_menu'] = 'sendnotification';
      
                
            if ($request->isMethod('post')) {
                /*echo "<pre>";
                print_r($request->all());
                echo "</pre>";
                die;*/
                $req_fields = [];
                $req_fields['title'] = 'required';
                $req_fields['description'] = 'required';
                $errormsg = [
                    'title' => translate('Title'),
                    'description' => translate('Description'),
                ];
                $validation = Validator::make(
                    $request->all(),
                    $req_fields,
                    [
                        'required' => 'The :attribute field is required.',
                    ],
                    $errormsg,
                );
                if ($validation->fails()) {
                    return back()->withErrors($validation)->withInput();
                }

                //$count = send_customer_notification($request->title,$request->description);
                $count = 0;
                $message = translate('Add Successfully');
                $status = 'success';
                $Sendnotifications = new Sendnotifications();                
                $Sendnotifications->title = $request->title;
                $Sendnotifications->description = $request->description;
                $Sendnotifications->count = $count;
                $Sendnotifications->created_at_time = time();
                $Sendnotifications->coupon_id = $request->coupon_id;
                $Sendnotifications->type = $request->type;
                $Sendnotifications->customer_advisor_group_id = $request->customer_advisor_group_id;
                $Sendnotifications->save();         

                $get_customers = Customers::where(['status' => 'Active'])->whereNotNull('fcm_token')->whereNull('is_delete');
                if ($request->type=='Customer') {
                    $get_customers->where(['type' => 'Customer']);
                }elseif ($request->type=='Advisor') {
                    $get_customers->where(['type' => 'Advisor']);
                }

                if ($request->customer_advisor_group_id && $request->customer_advisor_group_id!=''){
                    $Customergroups = Customergroups::where(['id'=>$request->customer_advisor_group_id,'status' => 'Active'])->whereNull('is_delete')->first();
                    if ($Customergroups && $Customergroups->customer_id) {
                        $customerIds = json_decode($Customergroups->customer_id, true);
                        $get_customers->whereIn('id', $customerIds);
                    }
                }

                $get_customers = $get_customers->get();
                dd($get_customers);

                return redirect()->route('admin.sendnotification')->withErrors([$status => $message]);
            }

            $Coupon = Coupon::whereNull('is_delete')->orderBy('id', 'desc')->get();

            return view('admin.sendnotification.store', compact('common','Coupon'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.sendnotification')
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function delete($id)
    {

        if (checkDecrypt($id) == false) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No Record Found']);
        }
        $id = checkDecrypt($id);
        $status = 'error';
        $message = 'Something went wrong!';
        $get_sendnotification = Sendnotifications::where(['id' => $id])->first();
        if ($get_sendnotification) {
            $get_sendnotification->is_delete = 1;
            $get_sendnotification->save();
        }
        $status = 'success';
        $message = 'Delete Successfully';
        return back()->withErrors([$status => $message]);
    }


    public function checktype(Request $request)
    {
        /*echo "<pre>";
        print_r($request->all());
        echo "</pre>";*/
        $customergroups = NULL;        
        $label = '';        
        if ($request->val=='Customer') {
            $label = 'Customer groups';        
           $customergroups = Customergroups::where(['status' => 'Active','type' => 'Customer'])->whereNull('is_delete')->orderBy('id', 'desc')->get();
        }elseif ($request->val=='Advisor') {
            $label = 'Advisor groups';        
            $customergroups = Customergroups::where(['status' => 'Active','type' => 'Customer'])->whereNull('is_delete')->orderBy('id', 'desc')->get();
        }

        $html = '';
        if ($customergroups) {
            $html .= '<div class="form-group mb-3">';
                $html .= '<label class="col-form-label" for="customer_advisor_group_id">'.$label.'</label>';
                $html .= '<select id="customer_advisor_group_id" name="customer_advisor_group_id" class="form-select single-select" data-allow-clear="true">';
                    $html .= '<option value="">All</option>';
                    foreach ($customergroups as $key => $value) {
                        $html .= '<option value="'.$value->id.'">'.$value->title.'</option>';
                    }
                $html .= '</select>';
            $html .= '</div>';
        }
        return $html;
    }
    
}