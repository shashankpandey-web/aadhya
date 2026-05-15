<?php
use App\Http\Controllers\Admin\AvailabilityController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PagesController;
use App\Http\Controllers\Admin\CustomersController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AdvisorController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\TarotController;
use App\Http\Controllers\Admin\CustomerreportController;
use App\Http\Controllers\Admin\CustomersupportController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\MonthlyOfferController;
use App\Http\Controllers\Admin\SendnotificationsController;
use Illuminate\Support\Facades\Route;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Balance;
/*
 * |--------------------------------------------------------------------------
 *
 * | Web Routes
 *
 * |--------------------------------------------------------------------------
 *
 * |
 *
 * | Here is where you can register web routes for your application. These
 *
 * | routes are loaded by the RouteServiceProvider within a group which
 *
 * | contains the "web" middleware group. Now create something great!
 *
 * |
 */

Route::get('/config-all', function () {
	$exitCode = Artisan::call('route:clear');
	$exitCode = Artisan::call('cache:clear');
	$exitCode = Artisan::call('config:clear');
	$exitCode = Artisan::call('config:cache');
	$exitCode = Artisan::call('view:clear');
	//$exitCode = Artisan::call('optimize:clear');
	echo date("d--m-Y H:i:s");
	return 'Config cache cleared';
});

/*Route::get('/config-all', function () {
	// phpinfo();

	Stripe::setApiKey(env('STRIPE_SECRET'));

	// Create a test charge to add funds
	$charge = Charge::create([
		'amount' => 10000, // Amount in cents ($100.00)
		'currency' => 'usd',
		'source' => 'tok_bypassPending', // Test card token to bypass pending status
		'description' => 'Test charge to simulate available balance',
	]);

	return response()->json([
		'status' => true,
		'message' => 'Funds added to available balance successfully.',
		'data' => $balance = Balance::retrieve(),
	]);

	// $exitCode = Artisan::call('route:clear');
	// $exitCode = Artisan::call('cache:clear');
	// $exitCode = Artisan::call('config:clear');
	// $exitCode = Artisan::call('config:cache');
	// $exitCode = Artisan::call('view:clear');
	// $exitCode = Artisan::call('optimize:clear');
	// echo "<br>";
	// echo env('APP_NAME');
	// echo "<br>";
	// return 'Config cache cleared';
});*/

Route::get('/email_demo', function () {

	$main_title      = "Hy Admin ";
	$subject_text    = "";
	$email           = "";
	$message_text    = "";
	$data['email']   = 'dev2.infosparkles@gmail.com';
	$data['name']    = 'Vishal';
	$data['subject'] = 'Hy Admin ';
	// Mail::send('email.new_advisor_mail', $data, function ($message) use ($data) {
	// 	$message->to($data['email'], $data['name'])
	// 		->subject($data['subject']);
	// });



	return view('email.new_advisor_mail', compact('subject_text', 'email', 'message_text'));
});


//Cashfree Payment Url
Route::get('/app.secure.payment.status.success/{payment_id}', [PaymentController::class, 'success'])->name('payment.status.success');
Route::get('/app.secure.payment.status.cancel/{payment_id}', [PaymentController::class, 'cancel'])->name('payment.status.cancel');

Route::match(['get', 'post'], '/fcm_token', [LoginController::class, 'fcm_token'])->name('admin.fcm_token');

Route::middleware(['admin_checkLogin'])->group(function () {
	Route::match(['get', 'post'], '/', [LoginController::class, 'index'])->name('admin.login');

	Route::match(['get', 'post'], '/forgot-password', [LoginController::class, 'forgot_password'])->name('admin.forgot_password');

	Route::match(['get', 'post'], '/reset-password/{user_id}', [LoginController::class, 'reset_password'])->name('admin.reset_password');
});
Route::match(['get', 'post'], '/delete-account', [LoginController::class, 'delete_account'])->name('admin.delete_account');

Route::group(['middleware' => 'adminAuth'], function () {
	Route::match(['get', 'post'], '/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
	Route::match(['get', 'post'], '/logout', [DashboardController::class, 'logout'])->name('admin.logout');
	Route::match(['get', 'post'], '/profile', [DashboardController::class, 'user_profile'])->name('admin.profile');
	// category
	Route::match(['get', 'post'], 'categories', [CategoryController::class, 'index'])->name('admin.categories');
	Route::match(['get', 'post'], 'category/add-edit/{id?}', [CategoryController::class, 'store'])->name('admin.categories.add');
	Route::get('category/delete/{id}', [CategoryController::class, 'delete'])->name('admin.categories.delete');	
	
	// Coupon
	Route::get('coupons', [CouponController::class, 'index'])->name('admin.coupon');
	Route::match(['get', 'post'], 'coupon/add_edit/{id?}', [CouponController::class, 'store'])->name('admin.coupon.add_edit');
	Route::get('coupon/delete/{id}', [CouponController::class, 'delete'])->name('admin.coupon.delete');

	// Sendnotification
    Route::get('sendnotification', [SendnotificationsController::class, 'index'])->name('admin.sendnotification');
    Route::match(['get', 'post'], 'sendnotification/add-edit', [SendnotificationsController::class, 'add'])->name('admin.sendnotification.addEdit');
    Route::get('sendnotification/delete/{id}', [SendnotificationsController::class, 'delete'])->name('admin.sendnotification.delete');
    Route::match(['get', 'post'], 'sendnotification/checktype', [SendnotificationsController::class, 'checktype'])->name('admin.sendnotification.checktype');

	// Customers
	Route::match(['get', 'post'], 'customers', [CustomersController::class, 'index'])->name('admin.customers');
	Route::match(['get', 'post'], 'customer/view/{id?}', [CustomersController::class, 'view'])->name('admin.customer.view');
	Route::match(['get', 'post'], 'customer/change_status', [CustomersController::class, 'change_status'])->name('admin.customer.change_status');
	Route::get('customer/delete/{id}', [CustomersController::class, 'delete'])->name('admin.customer.delete');

	// Customers groups
	Route::match(['get', 'post'], 'customergroups', [CustomersController::class, 'customergroups'])->name('admin.customergroups');
	Route::match(['get', 'post'], 'customergroup/add-edit/{id?}', [CustomersController::class, 'customergroups_store'])->name('admin.customergroup.add');
	Route::get('customergroup/delete/{id}', [CustomersController::class, 'customergroups_delete'])->name('admin.customergroup.delete');
	
	// availabilities
	Route::match(['get', 'post'], 'availabilities', [AvailabilityController::class, 'index'])->name('admin.availabilities');
	Route::match(['get', 'post'], 'availability/add-edit/{id?}', [AvailabilityController::class, 'store'])->name('admin.availabilities.add');
	Route::get('availability/delete/{id}', [AvailabilityController::class, 'delete'])->name('admin.availabilities.delete');

	// pages
	Route::get('pages', [PagesController::class, 'index'])->name('admin.pages');
	Route::match(['get', 'post'], 'edit/page/{id}', [PagesController::class, 'store'])->name('admin.pages.store');

	//advisor_withdraw_request
	Route::match(['get', 'post'], 'advisor_withdraw_request', [AdvisorController::class, 'index'])->name('admin.advisor_withdraw_request');
	Route::get('advisor_withdraw_request/view/{id}', [AdvisorController::class, 'get_request_detail'])->name('admin.advisor_withdraw_request.view');
	Route::match(['get', 'post'], 'withdrawal-request-payout', [AdvisorController::class, 'withdrawal_request_payout'])->name('admin.withdrawal_request_payout');
	Route::match(['get', 'post'], 'withdrawal-request-payout-new/{id}', [AdvisorController::class, 'withdrawal_request_payout_new'])->name('admin.withdrawal_request_payout_new');
	Route::match(['get', 'post'], 'advisor-list', [AdvisorController::class, 'advisor_list'])->name('admin.advisor_list');
	Route::match(['get', 'post'], 'advisor/view/{id?}', [AdvisorController::class, 'view'])->name('admin.advisor.view');
	Route::match(['get', 'post'], 'advisor-transaction-list/{id}', [AdvisorController::class, 'advisor_transaction_list'])->name('admin.advisor_transaction_list');


	Route::match(['get', 'post'], 'advisor-review-list', [AdvisorController::class, 'advisor_review_list'])->name('admin.advisor.review_list');
	Route::match(['get', 'post'], 'advisor-review-edit/{id?}', [AdvisorController::class, 'advisor_review_edit'])->name('admin.advisor.review_edit');
	Route::match(['get', 'post'], 'advisor-review_change_status', [AdvisorController::class, 'advisor_review_change_status'])->name('admin.advisor.review_change_status');


	// advisors groups
	Route::match(['get', 'post'], 'advisorgroups', [AdvisorController::class, 'advisorgroups'])->name('admin.advisorgroups');
	Route::match(['get', 'post'], 'advisorgroup/add-edit/{id?}', [AdvisorController::class, 'advisorgroups_store'])->name('admin.advisorgroup.add');
	Route::get('advisorgroup/delete/{id}', [AdvisorController::class, 'advisorgroups_delete'])->name('admin.advisorgroup.delete');

	//Settings
	Route::match(['get', 'post'], '/settings', [SettingController::class, 'settings'])->name('admin.settings');
	Route::match(['get', 'post'], '/monthly-offers', [MonthlyOfferController::class, 'index'])->name('admin.monthly_offers');


	// banner
	Route::match(['get', 'post'], 'banners', [BannerController::class, 'index'])->name('admin.banners');
	Route::match(['get', 'post'], 'banner/add-edit/{id?}', [BannerController::class, 'store'])->name('admin.banners.add');
	Route::get('banner/delete/{id}', [BannerController::class, 'delete'])->name('admin.banners.delete');

	// tarot
	Route::match(['get', 'post'], 'tarots', [TarotController::class, 'index'])->name('admin.tarots');
	Route::match(['get', 'post'], 'tarot/add-edit/{id?}', [TarotController::class, 'store'])->name('admin.tarots.add');
	Route::get('tarot/delete/{id}', [TarotController::class, 'delete'])->name('admin.tarots.delete');


	// customerreport
	Route::match(['get', 'post'], 'customerreports', [CustomerreportController::class, 'index'])->name('admin.customerreports');
	Route::match(['get', 'post'], 'customerreport/view/{id?}', [CustomerreportController::class, 'view'])->name('admin.customerreports.view');

	// customersupport
	Route::match(['get', 'post'], 'customersupports', [CustomersupportController::class, 'index'])->name('admin.customersupports');
	Route::match(['get', 'post'], 'customersupport/view/{id?}', [CustomersupportController::class, 'view'])->name('admin.customersupports.view');
	Route::match(['get', 'post'], 'customersupport/update', [CustomersupportController::class, 'update'])->name('admin.customersupports.update');

});
