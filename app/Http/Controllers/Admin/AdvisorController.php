<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerWallet;
use App\Models\AdvisorBanks;
use App\Models\Customers;
use App\Models\Customerdetail;
use App\Models\Customergroups;
use App\Traits\CashfreeTraits;
use App\Traits\PayPalTraits;
use App\Models\AdvisorStripeAccount;
use App\Models\Advisorreviews;
use App\Models\Countries;
use App\Models\States;
use App\Models\City;
use App\Models\CustomerCategories;
use App\Models\Categories;
use App\Models\Availability;
use App\Models\CustomerAvailabilities;
use Illuminate\Support\Facades\Validator;
use Mail;
use DB;

class AdvisorController extends Controller
{


    use PayPalTraits;
    use CashfreeTraits;
    public function index(Request $request)
    {
        // Common data for the view
        $common = [
            'title' => 'Advisor Request',
            'main_menu' => 'advisors',
            'submain_menu' => 'advisor_withdraw_request',
            'filter_availability_name' => '',
            'filter_availability_status' => ''
        ];


        $get_customer_wallet = [];
        // $getCustomerWallet = CustomerWallet::select(
        //     'customer_wallet.customer_id',
        //     'customer_wallet.admin_commision_percentage',
        //     'customer_wallet.admin_commision_amount',
        //     'customer_wallet.advisor_amount',
        //     DB::raw('SUM(admin_commision_amount) as total_admin_commission_amount'),
        //     DB::raw('SUM(advisor_amount) as total_advisor_amount'),
        //     DB::raw('SUM(amount) as total_amount'),
        //     'cs.full_name'
        // )
        //     ->whereNull('is_delete')
        //     ->leftJoin('customers as cs', 'cs.id', '=', 'customer_wallet.customer_id')
        //     ->where('cs.type', 'Advisor')
        //     ->groupBy(
        //         'customer_wallet.customer_id',
        //     )
        //     ->orderBy('cs.id', 'desc');

        // $getCustomerWallet = $getCustomerWallet->paginate(config('adminconfig.records_per_page'));


        $getCustomerWallet = CustomerWallet::select(
            'customer_wallet.id',
            'customer_wallet.customer_id',
            'customer_wallet.admin_commision_percentage',
            'customer_wallet.admin_commision_amount',
            'customer_wallet.withdrawal_status',
            'customer_wallet.amount',
            'customer_wallet.is_withdrawal',
            'customer_wallet.request_number',
            'customer_wallet.created_at',
            'cs.full_name',
            'cs.email',
            'ab.account_holder_name',
            'ab.bank_name',
            'ab.account_number',
        )
            ->leftJoin('customers as cs', 'cs.id', '=', 'customer_wallet.customer_id')
            ->leftJoin('advisor_banks as ab', 'ab.customer_id', '=', 'customer_wallet.customer_id')
            ->where('cs.type', 'Advisor')
            ->where('customer_wallet.is_withdrawal', 1)
            ->where('customer_wallet.withdrawal_status', 'PENDING')
            ->orwhere('customer_wallet.withdrawal_status', 'SUCCESS')
            ->groupBy('customer_wallet.id')
            ->orderBy('cs.id', 'desc');

        $getCustomerWallet = $getCustomerWallet->paginate(config('adminconfig.records_per_page'));
        $getCustomerWallet->transform(function ($customerWallet) {
            $customerWallet->remaining_wallet_amount  = CustomerWallet::getTotalAmount($customerWallet->customer_id);
            $customerWallet->date = $customerWallet->date ? $customerWallet->date : '';
            return $customerWallet;
        });
        return view('admin.advisor.advisor_withdrawal_requests.index', compact('common', 'getCustomerWallet'));
    }

    public function get_request_detail(Request $request, $id)
    {
        // Common data for the view
        $common = [
            'title'                      => 'Advisor Request Detail',
            'main_menu'                  => 'advisor_withdraw_request',
            'submain_menu'               => 'advisor_withdraw_request',
            'filter_availability_name'   => '',
            'filter_availability_status' => ''
        ];

        $id = checkDecrypt($id);

        $getCustomerWallet = CustomerWallet::select(
            'customer_wallet.customer_id',
            'customer_wallet.admin_commision_percentage',
            'customer_wallet.admin_commision_amount',
            'customer_wallet.advisor_amount',
            DB::raw('SUM(admin_commision_amount) as total_admin_commission_amount'),
            DB::raw('SUM(advisor_amount) as total_advisor_amount'),
            DB::raw('SUM(amount) as total_amount'),
            'cs.full_name'
        )
            ->whereNull('is_delete')
            ->leftJoin('customers as cs', 'cs.id', '=', 'customer_wallet.customer_id')
            ->where('cs.type', 'Advisor')
            ->groupBy(
                'customer_wallet.customer_id',
            )
            ->orderBy('cs.id', 'desc');
        return view('admin.advisor.advisor_withdrawal_requests.index', compact('common', 'getCustomerWallet'));
    }

    /*public function withdrawal_request_payout(Request $request)
    {
        $err        = [];
        $err['id']  = 'required';
        $validation = Validator::make($request->all(), $err);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
        }

        $wallet_id         = $request->id;
        $getCustomerWallet = CustomerWallet::where('id', $wallet_id)->where('is_withdrawal', '1')->where('withdrawal_status', 'PENDING')->first();
        if ($getCustomerWallet) {
            $getAdvisor = Customers::where('id', $getCustomerWallet['customer_id'])->first();
            if ($getAdvisor) {
                $AdvisorStripeAccount = AdvisorStripeAccount::where('advisor_id', $getAdvisor['id'])->first();
                if ($AdvisorStripeAccount) {

                    if ($AdvisorStripeAccount->stripe_acc_id && $getCustomerWallet->bank_id) {
                        $AdvisorBanks = AdvisorBanks::where('customer_id', $getCustomerWallet['customer_id'])->where('id', $getCustomerWallet->bank_id)->first();
                        if ($AdvisorBanks && $AdvisorBank->stripe_bank_id) {
                            $data = [];
                            $data['amount']         = $getCustomerWallet['amount'];
                            $data['stripe_acc_id']  = $AdvisorStripeAccount['stripe_acc_id'];
                            $data['stripe_bank_id'] = $AdvisorBank->stripe_bank_id;
                            $responseData = $this->create_payout($data);
                            if (!isset($responseData['id'])) {
                                return response()->json(['status' => false, 'message' => $responseData['message']], 500);
                            }
                            $getCustomerWallet->payout_id = $responseData['id'];
                            $getCustomerWallet->withdrawal_status = 'PAYOUT_PENDING';
                            $getCustomerWallet->payment_json      = json_encode($responseData);
                            $getCustomerWallet->save();
                            return response()->json(['status' => true, 'message' => 'Payout Send Successfully']);
                        }
                        return response()->json(['status' => true, 'message' => 'Bank are not exists']);
                    }
                    return response()->json(['status' => true, 'message' => 'Bank are not exists']);
                }
                return response()->json(['status' => true, 'message' => 'Stripe Account are not exists']);
            }
            return response()->json(['status' => false, 'message' => 'Bank Details Not Verified']);
        }
        return response()->json(['status' => false, 'message' => 'Request Not Found']);
    }*/
    
    public function withdrawal_request_payout_new(Request $request,$id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
        }

        $wallet_id = checkDecrypt($id);
        $getCustomerWallet = CustomerWallet::where('id', $wallet_id)->where('is_withdrawal', '1')->where('withdrawal_status', 'PENDING')->first();
        if ($getCustomerWallet) {
            $getAdvisor = Customers::where('id', $getCustomerWallet['customer_id'])->first();
            if ($getAdvisor) {
                $status = 'success';
                $getCustomerWallet->withdrawal_status = 'SUCCESS';
                $getCustomerWallet->save();

                $data = array(); 
                $data['name']  = $getAdvisor->full_name;
                $data['email'] = $getAdvisor->email;
                $data['phone_number']  = $getAdvisor->phone_number;

                $data['subject']       = 'Your withdrawal has been successfully processed';
                $data['template']      = 'email.withdrawal_request_payout';
                if ($data['template']) {
                    Mail::send($data['template'], $data, function ($message) use ($data) {
                        $message->to($data['email'], $data['name'])->subject($data['subject']);
                    });
                }


                $message = 'Your payout of '.$getCustomerWallet['amount'].' has been successfully initiated.';
                return redirect()->route('admin.advisor_withdraw_request')->withErrors([$status => $message]);
            }
            return redirect()->back()->withErrors(['error' => translate('User Not Found')]);
        }
        return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
    }


    public function advisor_list(Request $request)
    {
        $common = [
            'title'                      => 'Advisor list',
            'main_menu'                  => 'advisors',
            'submain_menu'               => 'advisor_list',
            'filter_availability_name'   => '',
            'filter_availability_status' => ''
        ];

        $get_all_advisors        = [];
        $common['filter_title']  = '';
        $common['filter_status'] = '';

        if ($request->isMethod('post')) {
            $filterFields = ['filter_title', 'filter_status', 'filter_usertype'];
            $common = updateSessionFilters($request, $filterFields, $common);
        }

        $get_all_advisors = Customers::select(
            'customers.id',
            'customers.full_name',
            'customers.email',
            'customers.date_of_birth',
            'customers.status as customer_status',
            'customers.phone_number',
            'customers.image',
            'customers.type',
            'cw.id as cw_id',
            'cw.admin_commision_amount',
            'cw.withdrawal_status',
            'cw.amount',
            'cw.is_withdrawal',
            DB::raw('SUM(CASE WHEN is_withdrawal IS NULL THEN amount ELSE 0 END) as total_wallet_amount'),
            DB::raw('SUM(CASE WHEN is_withdrawal = 1 THEN amount ELSE 0 END) as withdrawal_wallet_amount'),
            DB::raw('SUM(CASE WHEN is_withdrawal IS NULL THEN amount ELSE 0 END) - SUM(CASE WHEN is_withdrawal = 1 THEN amount ELSE 0 END) as wallet_amount'),
        )
            ->whereNull('customers.is_delete')
            ->whereNull('customers.step')
            ->where('customers.type', 'Advisor')
            ->leftJoin('customer_wallet as cw', 'cw.customer_id', '=', 'customers.id');
        if ($common['filter_title']) {
            $get_all_advisors->where('customers.full_name', 'like', '%' . $common['filter_title'] . '%');
        }
        $get_all_advisors = $get_all_advisors->groupBy('customers.id')->orderBy('customers.id', 'desc')->paginate(config('adminconfig.records_per_page'));

        return view('admin.advisor.index', compact('common', 'get_all_advisors'));
    }


    public function view(Request $request, $id = '')
    {
        $common = array();
        $common = [
            'title' => 'Advisor',
            'main_menu' => 'advisors',
            'submain_menu' => 'advisor_list',
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


                $customer_category_arr = [];
                $get_customer_categories = CustomerCategories::where(['customer_id' => $get_customer->id])->get();
                if ($get_customer_categories) {
                    foreach ($get_customer_categories as $key => $value) {
                        $get_customer_category = Categories::where(['status' => 'Active'])->whereNull('is_delete')->where(['id' => $value->caterogy_id])->first();
                        if ($get_customer_category) {
                            $customer_category_arr[] = $get_customer_category->title;
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

                    $availability['id']              = NULL;
                    $availability['charges']         = 0;
                    $availability['status']       = 'Deactive';

                    $CustomerAvailabilities = CustomerAvailabilities::where(['availability_id' => $value1->id,'customer_id' => $get_customer->id])->first();
                    if ($CustomerAvailabilities) {
                        $availability['id']              = $CustomerAvailabilities->id;
                        $availability['charges']         = $CustomerAvailabilities->charges ? $CustomerAvailabilities->charges : 0;
                        $availability['status']       = isset($CustomerAvailabilities->status) ? $CustomerAvailabilities->status : '';
                    }
                    
                    $availability_arrs[]        = $availability;
                }

                $Customerdetail = Customerdetail::where('customer_id', $get_customer->id)->first();
                if (!$Customerdetail) {
                    $Customerdetail = (object) getTableColumn('customer_detail');
                }

                return view('admin.advisor.view', compact('common', 'get_customer','customer_category_arr','availability_arrs','Customerdetail'));
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

    public function advisor_transaction_list(Request $request, $id)
    {
        $common = [
            'title'                      => 'Advisor Transaction list',
            'main_menu'                  => 'advisors',
            'submain_menu'               => 'advisor_list',
            'filter_availability_name'   => '',
            'filter_availability_status' => ''
        ];

        $get_all_advisors        = [];
        $common['filter_title']  = '';
        $common['filter_status'] = '';

        if ($request->isMethod('post')) {
            $filterFields = ['filter_title', 'filter_status', 'filter_usertype'];
            $common = updateSessionFilters($request, $filterFields, $common);
        }

        $id = checkDecrypt($id);
        $getAdvisorTransactionList = CustomerWallet::select(
            'customer_wallet.id',
            'customer_wallet.admin_commision_percentage',
            'customer_wallet.admin_commision_amount',
            'customer_wallet.amount',
            'customer_wallet.total_amount',
            'customer_wallet.type',
            'customer_wallet.advisore_availability_id',
            'customer_wallet.is_withdrawal',
            'customer_wallet.withdrawal_status',
            'customer_wallet.created_at',
            'ca.id',
            'ca.availability_id',
            DB::raw('CASE WHEN is_withdrawal = "1" THEN "Withdrawal amount" ELSE title END as transaction_title'),
            DB::raw('CASE WHEN is_withdrawal = "1" THEN withdrawal_status ELSE "SUCCESS" END as transaction_status'),
        )
            ->leftJoin('customer_availabilities as ca', 'ca.id', '=', 'customer_wallet.advisore_availability_id')
            ->leftJoin('availabilities as aa', 'aa.id', '=', 'ca.availability_id')
            ->where('customer_wallet.customer_id', $id)
            ->orderby('customer_wallet.id', 'desc')
            ->paginate(config('adminconfig.records_per_page'));
        // echo "ID:".$id;
        // echo "<br>";
        // print_die($getAdvisorTransactionList->toArray());

        return view('admin.advisor.transaction_history', compact('common', 'getAdvisorTransactionList'));
    }


    public function advisor_review_list(Request $request)
    {
        $common = [
            'title'                      => 'Advisor Review list',
            'main_menu'                  => 'advisors',
            'submain_menu'               => 'advisor_review_list',
        ];

        $common['filter_advisor_review_customer'] = '';
        $common['filter_advisor_review_advisor'] = '';
        $common['filter_advisor_review_status'] = '';

        $filterFields = ['filter_advisor_review_customer','filter_advisor_review_advisor','filter_advisor_review_status'];
        $common = updateSessionFilters($request, $filterFields, $common);


        $get_all_Advisorreviews = Advisorreviews::select('*');

        if ($common['filter_advisor_review_customer']) {
            $get_all_Advisorreviews->where('customer_id', $common['filter_advisor_review_customer']);
        }
        if ($common['filter_advisor_review_advisor']) {
            $get_all_Advisorreviews->where('advisore_id', $common['filter_advisor_review_advisor']);
        }
        if ($common['filter_advisor_review_status']) {
            $get_all_Advisorreviews->where('status', $common['filter_advisor_review_status']);
        }

        $get_all_Advisorreviews = $get_all_Advisorreviews->orderBy('id', 'desc')->paginate(config('adminconfig.records_per_page'));

        $Advisorreviews =  array();
        foreach ($get_all_Advisorreviews as $key => $value) {
            $row =  array();
            $row['id'] =  $value->id;
            $row['rating'] =  $value->rating;
            $row['rating'] =  $value->rating;
            $row['status'] =  $value->status;

            $row['customer_name'] =  '';
            $Customers = Customers::where('id', $value->customer_id)->first();
            if ($Customers) {
                $row['customer_name'] = $Customers->full_name;
            }

            $row['advisor_name'] =  '';
            $Customers = Customers::where('id', $value->advisore_id)->first();
            if ($Customers) {
                $row['advisor_name'] = $Customers->full_name;
            }

            $Advisorreviews[] =  $row;
        }

        $Advisor = Customers::whereNull('is_delete')->whereNull('step')->where('type', 'Advisor')->orderBy('id', 'desc')->get();
        $Customer = Customers::whereNull('is_delete')->where('type', 'Customer')->orderBy('id', 'desc')->get();

        return view('admin.advisor.advisor_review.index', compact('common', 'get_all_Advisorreviews','Advisorreviews','Advisor','Customer'));
    }

    public function advisor_review_edit(Request $request, $id = '')
    {
        $common = array();
        $common = [
            'title' => 'Advisor Review Edit',
            'main_menu'      => 'advisors',
            'submain_menu'               => 'advisor_review_list',
        ];

        if ($request->isMethod('post')) {
            $req_fields = array();
            $req_fields['rating'] = 'required';
            $req_fields['status'] = 'required';
            $req_fields['rating_text'] = 'required';

            $errormsg = [
                'rating' => translate('Rating'),
                'status' => translate('Status'),
                'rating_text' => translate('Rating text'),
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
                $Advisorreviews = Advisorreviews::find($request->id);
            } else {
                $message = translate('Add Successfully');
                $status = 'success';
                $Advisorreviews = new Advisorreviews();
            }

            
            $Advisorreviews->rating = $request->rating;
            $Advisorreviews->status = $request->status;
            $Advisorreviews->rating_text = $request->rating_text;
            $Advisorreviews->save();

            return redirect()->route('admin.advisor.review_list')->withErrors([$status => $message]);
        }

        $Advisorreviews = getTableColumn('advisor_reviews');
        
        $customer_name =  '';
        $advisor_name =  '';
        if ($id != '') {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
            }
            $id = checkDecrypt($id);
            $Advisorreviews = Advisorreviews::where('id', $id)->first();
            if (!$Advisorreviews) {
                return back()->withErrors(['error' => translate('Something went wrong')]);
            }

            $Customers = Customers::where('id', $Advisorreviews->customer_id)->first();
            if ($Customers) {
                $customer_name = $Customers->full_name;
            }
            $Customers = Customers::where('id', $Advisorreviews->advisore_id)->first();
            if ($Customers) {
                $advisor_name = $Customers->full_name;
            }
        }else{
            return back()->withErrors(['error' => translate('Something went wrong')]);
        }        
        return view('admin.advisor.advisor_review.store', compact('common', 'Advisorreviews','customer_name','advisor_name'));
    }

    public function advisor_review_change_status(Request $request)
    {
        $status = 'error';
        $message = translate('Something went wrong!');
        if (isset($request->id)) {
            $id = $request->id;
            $Advisorreviews = Advisorreviews::where(['id' => $id])->first();
            if ($Advisorreviews) {
                $Advisorreviews->status = $Advisorreviews->status == 'Active' ? 'Deactive' : 'Active';
                $Advisorreviews->save();
                $status  = $Advisorreviews->status;
                $message = translate('Status Change Successfully');
            }
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
        ], 200);
    }


    public function advisorgroups(Request $request)
    {
        $common = array();
        $common['title'] = 'Advisor groups';
        $common['main_menu'] = 'advisors';
        $common['submain_menu'] = 'advisorgroup';
        $common['filter_advisorgroup_title'] = '';
        $common['filter_advisorgroup_status'] = '';

        if ($request->isMethod('post')) {
            $filterFields = ['filter_advisorgroup_title', 'filter_advisorgroup_status'];
            $common = updateSessionFilters($request, $filterFields, $common);
        }

        $advisorgroups = Customergroups::where(['type' => 'Advisor'])->whereNull('is_delete')->orderBy('id', 'desc');

        if ($common['filter_advisorgroup_title']) {
            $advisorgroups->where('title', 'like', '%' . $common['filter_advisorgroup_title'] . '%');
        }

        if ($common['filter_advisorgroup_status']) {
            $advisorgroups->where('status', $common['filter_advisorgroup_status']);
        }

        $advisorgroups = $advisorgroups->paginate(config('adminconfig.records_per_page'));

        return view('admin.advisorgroup.index', compact('common', 'advisorgroups'));
    }


    public function advisorgroups_store(Request $request, $id = '')
    {
        $common = array();
        $common = [
            'title' => 'Advisor group',
            'main_menu' => 'advisors',
            'submain_menu' => 'advisorgroup',
        ];

        $get_advisorgroup = getTableColumn('customer_groups');
        
        if ($id != '') {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
            }
            $id = checkDecrypt($id);
            $get_advisorgroup = Customergroups::where('id', $id)->first();
            if (!$get_advisorgroup) {
                return back()->withErrors(['error' => translate('Something went wrong')]);
            }
        }

        if ($request->isMethod('post')) {
            $req_fields = array();
            $req_fields['title'] = 'required';
            $req_fields['customer_id']   = "required";

            $errormsg = [
                'title' => translate('Title'),
                'customer_id' => translate('Advisor'),
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
            $Customergroup->type = 'Advisor';
            $Customergroup->save();

            return redirect()->route('admin.advisorgroups')->withErrors([$status => $message]);
        }

        $get_customers = Customers::where(['type' => 'Advisor','status' => 'Active'])->whereNull('is_delete')->orderBy('id', 'desc')->get();
        return view('admin.advisorgroup.store', compact('common', 'get_advisorgroup', 'get_customers'));
    }

    public function advisorgroups_delete($id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(['error' => translate('No Record Found')]);
        }
        $id = checkDecrypt($id);
        $status = 'error';
        $message = translate('Something went wrong!');
        $get_advisorgroup = Customergroups::where(['id' => $id,'type' => 'Advisor'])->whereNull('is_delete')->first();
        if ($get_advisorgroup) {
            $get_advisorgroup->is_delete = 1;
            $get_advisorgroup->save();
        }
        $status = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }
}
