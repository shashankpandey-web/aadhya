<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\InformationController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\WishlistController;
/* Advisor Controller */
use App\Http\Controllers\Api\AdvisorController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CustomerreportController;
use App\Http\Controllers\Api\WebhookController;



/*

|--------------------------------------------------------------------------

| API Routes

|--------------------------------------------------------------------------

|

| Here is where you can register API routes for your application. These

| routes are loaded by the RouteServiceProvider within a group which

| is assigned the "api" middleware group. Enjoy building your API!

|

*/


Route::middleware('auth:api')->post('/broadcasting/auth', function (Request $request) {
    \Log::info('Authorization Header:', [$request->header('Authorization')]);
    return Broadcast::auth($request);
});

Route::any('/refresh_token', [LoginController::class, 'refreshToken'])->name('refreshToken');
// Route::any('/refreshToken2', [LoginController::class, 'refreshToken2'])->name('refreshToken2');

Route::middleware(['api'])->group(function () {

    //Route::any('/login', [LoginController::class, 'login'])->name('API_login');
    Route::any('login', [LoginController::class, 'login']);
    Route::any('/signup', [LoginController::class, 'signup'])->name('API_signup');
    Route::any('/advisor-signup', [LoginController::class, 'advisor_signup'])->name('API_advisor_signup');
    Route::any('/social-login', [LoginController::class, 'social_login'])->name('social_login');
    Route::any('/forgotpasword', [LoginController::class, 'forgotpasword'])->name('API_forgotpasword');
    Route::any('/verifyotp', [LoginController::class, 'verifyotp'])->name('API_verifyotp');
    Route::any('/resetpasword', [LoginController::class, 'resetpasword'])->name('resetpasword');

    Route::any('/countries', [CommonController::class, 'countries'])->name('countries');
    Route::any('/states', [CommonController::class, 'states'])->name('states');
    Route::any('/cities', [CommonController::class, 'cities'])->name('cities');
    Route::any('/categories', [CommonController::class, 'categories'])->name('categories');
    Route::any('/availabilities', [CommonController::class, 'availabilities'])->name('availabilities');

    Route::middleware('access.token')->group(function () {

        Route::any('profile1', [LoginController::class, 'profile1']);
        Route::any('/profile', [LoginController::class, 'profile'])->name('API_profile');
        Route::any('/logout', [LoginController::class, 'logout'])->name('API_logout');
        Route::any('/profileupdate', [LoginController::class, 'profileupdate'])->name('API_profileupdate');
        Route::any('/changepassword', [LoginController::class, 'changepassword'])->name('API_changepassword');
        Route::any('/deletemyaccount', [LoginController::class, 'Deletemyaccount'])->name('API_deletemyaccount');

        Route::any('/is_missed_call', [LoginController::class, 'is_missed_call'])->name('API_is_missed_call');


        /* Home Controller */
        Route::any('/home', [HomeController::class, 'customer_home'])->name('customer_home');
        Route::any('/advisores', [HomeController::class, 'all_advisores'])->name('all_advisores');
        Route::any('/advisore-detail', [HomeController::class, 'advisore_detail'])->name('advisore_detail');
        Route::any('/add-wallet', [HomeController::class, 'add_wallet'])->name('add_wallet');

        /* Wishlist Controller */
        Route::any('/add-to-wishlist', [WishlistController::class, 'add_to_wishlist'])->name('add_to_wishlist');
        Route::any('/remove-wishlist', [WishlistController::class, 'remove_to_wishlist'])->name('remove_to_wishlist');
        Route::any('/favorite-advisores', [WishlistController::class, 'favorite_advisores'])->name('favorite_advisores');

        /* Common Controller */

        Route::any('/sale-status', [CommonController::class, 'sale_status'])->name('sale_status');
        Route::any('/app-rating', [CommonController::class, 'app_rating'])->name('app_rating');
        Route::any('/contact-us', [CommonController::class, 'contact_us'])->name('contact_us');
        Route::any('/add-advisor-review', [CommonController::class, 'add_advisor_review'])->name('add_advisor_review');
        Route::any('/advisor-reviews', [CommonController::class, 'advisor_reviews'])->name('advisor_reviews');

        Route::any('/validate-coupon', [OrderController::class, 'validate_coupon'])->name('validate_coupon');
        Route::any('/place-order', [OrderController::class, 'place_order'])->name('place_order');
        Route::any('/place-order-advisor', [OrderController::class, 'place_order_advisor'])->name('place_order_advisor');
        Route::any('/place-extend', [OrderController::class, 'place_extend'])->name('place_extend');
        Route::any('/transactions', [OrderController::class, 'transactions'])->name('transactions');
        Route::any('/withdrawal', [OrderController::class, 'withdrawal'])->name('withdrawal');
        Route::any('/my-orders', [OrderController::class, 'my_orders'])->name('my_orders');
        Route::any('/order-detail', [OrderController::class, 'order_detail'])->name('order_detail');
        Route::any('/get-adviser-wallet-detail', [OrderController::class, 'get_adviser_wallet_detail'])->name('get_adviser_wallet_detail');
        Route::any('/check-call-wallet-amount', [OrderController::class, 'check_call_wallet'])->name('check_call_wallet');
        Route::post('/paypal/create-order', [OrderController::class, 'createPaypalOrder']);
        Route::post('/paypal/capture-order', [OrderController::class, 'capturePaypalOrder']);

        Route::any('/advisor-home', [AdvisorController::class, 'advisor_home'])->name('advisor_home');
        Route::any('/change-advisor-rates', [AdvisorController::class, 'change_advisor_rates'])->name('change_advisor_rates');
        Route::any('/advisor-stripe-create-account', [AdvisorController::class, 'createOrUpdateStripeAccount'])->name('createOrUpdateStripeAccount');
        Route::any('/advisor-get-stripe-account', [AdvisorController::class, 'getStripeAccountDetail'])->name('getStripeAccountDetail');
        Route::any('/add-bank-details', [AdvisorController::class, 'add_bank_details'])->name('add_bank_details');
        Route::any('/get-bank-details', [AdvisorController::class, 'get_bank_detail'])->name('get_bank_details');
        Route::any('/get-bank-detail-list', [AdvisorController::class, 'get_bank_detail_list'])->name('get_bank_detail_list');
        Route::any('/delete-bank-detail', [AdvisorController::class, 'delete_bank_detail'])->name('delete_bank_detail');

        //Advisor My Orders
        Route::post('/advisor-my-order', [AdvisorController::class, 'advisor_my_order']);
        Route::post('/advisor-my-order-detail', [AdvisorController::class, 'advisor_my_orde_detail']);
        Route::post('/advisor-my-order-detail-services', [AdvisorController::class, 'advisor_my_orde_detail_services']);
        Route::post('/advisor-my-order-services-count', [AdvisorController::class, 'advisor_my_services_count']);
        Route::post('/advisor-my-order-chat', [AdvisorController::class, 'advisor_my_order_chat']);
        Route::post('/add-order-chat', [AdvisorController::class, 'add_order_chat']);
        Route::post('/adviser-todo-orders', [AdvisorController::class, 'adviser_to_do_orders']);
        Route::post('/advisor-earnings-chart', [AdvisorController::class, 'advisor_earnings_chart']);

        // Chat 
        // Route::get('/get-customers', [ChatController::class, 'getCustomers']);
        Route::post('/getmessages', [ChatController::class, 'getMessages']);
        Route::post('/mark-seen/{senderId?}', [ChatController::class, 'markMessagesAsSeen']);
        Route::post('/send-message', [ChatController::class, 'sendMessage']);
        Route::post('/create_agora_token', [ChatController::class, 'createAgoraToken']);
        Route::post('/send_notification', [ChatController::class, 'sendNotification']);


        Route::post('/notification-list', [InformationController::class, 'notification_list']);


        Route::any('/tarot', [CustomerreportController::class, 'tarot'])->name('tarot');
        Route::any('/tarotseen', [CustomerreportController::class, 'tarotseen'])->name('tarotseen');
        Route::any('/banner', [CustomerreportController::class, 'banner'])->name('banner');
        Route::any('/mycustomerreport', [CustomerreportController::class, 'mycustomerreport'])->name('mycustomerreport');
        Route::any('/customerreport', [CustomerreportController::class, 'customerreport'])->name('customerreport');

        Route::any('/mycustomesupport', [CustomerreportController::class, 'mycustomesupport'])->name('mycustomesupport');
        Route::any('/customesupport', [CustomerreportController::class, 'customesupport'])->name('customesupport');
    });
});

Route::any('/paypal/success-order', [OrderController::class, 'createPaypalSuccess']);
Route::any('/paypal/cancel-order', [OrderController::class, 'createPaypalCancel']);

Route::post('/check-payment-stripe-webhook', [WebhookController::class, 'handleStripePayment']);
Route::post('/check-payout-stripe-webhook', [WebhookController::class, 'handleStripePayout']);

Route::post('/check-payment-webhook/{wallet_id}', [WebhookController::class, 'checkPaymentWebhook']);
Route::post('/check-payout-webhook', [WebhookController::class, 'checkPayoutWebhook']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('/become-advisor', [InformationController::class, 'become_advisor']);
Route::any('/application-process', [InformationController::class, 'application_process']);
Route::any('/terms-conditions', [InformationController::class, 'terms_conditions']);

Route::any('/test_login', [LoginController::class, 'test_login'])->name('test_login');
Route::any('/test_signup', [LoginController::class, 'test_signup'])->name('test_signup');
Route::any('/test_profile', [LoginController::class, 'test_profile'])->name('test_profile');


//Route::get('/notification_test1', [ChatController::class, 'notification_test1'])->name('API_notification_test1');