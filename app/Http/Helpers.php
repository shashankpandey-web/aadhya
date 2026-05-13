<?php
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Filesystem\Filesystem;
use App\Models\Chatkeys;

use App\Models\Settings;


if (!function_exists('print_die')) {
    function print_die($arr = [])
    {
        echo '<pre>';
        print_r($arr);
        echo '<pre/>';
        die();
    }
}

// get Tablee Column
if (!function_exists('getTableColumn')) {
    function getTableColumn($table)
    {
        $tableColumn = Schema::getColumnListing($table);
        foreach ($tableColumn as $TC) {
            $ColumnArr[$TC] = '';
        }
        return $ColumnArr;
    }
}

// Decrypt Data
if (!function_exists('checkDecrypt')) {
    function checkDecrypt($el)
    {
        try {
            return Crypt::decrypt($el);
        } catch (DecryptException $e) {
            return false;
        }
    }
}

// get Languages
if (!function_exists('translate')) {
    function translate($el)
    {
        return $el;
    }
}

// Append Status Badge
if (!function_exists('checkStatus')) {
    function checkStatus($status)
    {
        if ($status == 'Active' || $status == 'active' || $status == 'verified' || $status == 'success' || $status == 'Success' || $status == 'SUCCESS' ||
            $status == "Credited" || $status == "Resolved" || $status == "Closed") {
            return "<span class='badge bg-label-success'>" . strtoupper($status) . '</span>';
        } elseif ($status == 'Draft' || $status == 'draft' || $status == "Pending" || $status == "In Progress") {
            return "<span class='badge bg-label-warning'>" . strtoupper($status) . '</span>';
        } else {
            return "<span class='badge bg-label-danger'>" . strtoupper($status) . '</span>';
        }
    }
}

// Get Selected VAlue
if (!function_exists('getSelected')) {
    function getSelected($option, $value)
    {
        if ($option == $value) {
            return ' selected';
        }
    }
}

// Get Checked VAlue
if (!function_exists('getChecked')) {
    function getChecked($option, $value)
    {
        if ($option == $value) {
            return ' checked';
        }
    }
}

// Get PRoduct Settting Value
if (!function_exists('getSelectedInArray')) {
    function getSelectedInArray($value, $array)
    {
        if (!is_array($array)) {
            $array = explode(',', $array);
        }

        if (in_array($value, $array)) {
            return ' selected';
        }
    }
}

if (!function_exists('image_upload_s3')) {
    function image_upload_s3($img,$path){
        $random_no  = uniqid();
        $ext        = $img->getClientOriginalExtension();
        $new_name   = time().'_'.$random_no . '.' . $ext;
        $upload_path = $img->storeAs(
            $path.'/'.date('Y').'/'.date('m'),
            $new_name,
            's3'
        );
        return $upload_path;
    }
}

if (!function_exists('get_image_upload_s3')) {
    function get_image_upload_s3($img){   
        $expiryDate = now()->addDay(); //The link will be expire after 1 day
        $temporaryUrl = Storage::disk('s3')->temporaryUrl($img, $expiryDate);
        return $temporaryUrl;
    }
}

if (!function_exists('image_upload')) {
    function image_upload($img, $path, $oldImage = null)
    {

        if ($oldImage) {
            image_delete($path . $oldImage);
        }

        $random_no  = uniqid();
        $ext        = $img->getClientOriginalExtension();
        $new_name   = time() . '_' . $random_no . '.' . $ext;
        /*$upload_path = $img->storeAs(
            $path,
            $new_name,
        );*/
        $destinationPath = public_path($path);
        $upload_path = $img->move($destinationPath, $new_name);
        if ($upload_path) {
            return $new_name;
        }
    }
}

/*if (!function_exists('image_upload')) {
    function image_upload($img, $path, $oldImage = null)
    {
        if ($oldImage) {
            image_delete($path.$oldImage);
        }
        
        $random_no  = uniqid();
        $mime_type  = $img->getMimeType();
        $ext        = $img->getClientOriginalExtension();
        $new_name   = time() . '_' . $random_no . '.' . $ext;
        $destinationPath = public_path($path);
        // Create a thumbnail
        $thumbnailPath = $destinationPath.'thumbnails_' . $new_name;
        $img2 = Image::make($img->getRealPath());
        $img2->resize(400, 400, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img2->save($thumbnailPath);
        // Save the original image
        $img->move($destinationPath, $new_name);
        return $new_name;
    }
}*/

if (!function_exists('image_delete')) {
    function image_delete($image_name)
    {
        if ($image_name != '') {
            $image_path = public_path($image_name);
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }
}

if (!function_exists('get_setting_data')) {
    function get_setting_data($meta_title = '', $column = '', $condition = '')
    {
        $value = '';
        if ($column == '') {
            $column = 'content';
        }
        if ($meta_title != '') {
            $get_setting = Settings::where('meta_title', $meta_title);
            if ($condition != '') {
                $get_setting->where('child_meta_title', $condition);
            }
            $get_setting = $get_setting->orderby('id', 'desc')->first();
            if ($get_setting) {
                if ($get_setting[$column] != 'Empty') {
                    if ($get_setting[$column]) {
                        $value = $get_setting[$column];
                    }
                }
            }
        }
        return $value;
    }
}

if (!function_exists('updateSessionFilters')) {
    function updateSessionFilters(Request $request, array $filterFields, array $common)
    {
        foreach ($filterFields as $field) {
            if ($request->isMethod('post')) {
                if ($request->has('reset')) {
                    Session::forget($field);
                }

                if ($request->has('is_filter')) {
                    Session::put($field, $request->$field);
                }
            }
            if (session($field)) {
                $common[$field] = session($field);
            }
        }
        return $common;
    }
}

if (!function_exists('get_calculate_amount')) {
    function get_calculate_amount($amount)
    {
        $advisor_amount            = 0;
        $admin_commison_amount      = 0;
        $_admin_commison_percentage = 0;

        $admin_commison_percentage  = get_setting_data('admin_commison_percentage', 'content');
        if ($admin_commison_percentage) {
            $_admin_commison_percentage = $admin_commison_percentage;
        }

        if ($amount > 0) {
            $admin_commison_amount = ($amount / 100) * $admin_commison_percentage;

            if ($admin_commison_amount < $amount) {
                $advisor_amount = $amount - $admin_commison_amount;
            }
        }

        return [
            'admin_commison_percentage' => $_admin_commison_percentage,
            'admin_commison_amount' => $admin_commison_amount,
            'advisor_amount' => $advisor_amount,
        ];
    }
}


if (!function_exists('fcm_generateJWT')) {
    function fcm_generateJWT()
    {
        $serviceAccountKeyFile =  url('aadya-64bb1-firebase-adminsdk-fbsvc-6fe9c8fe99.json');
        $serviceAccount = json_decode(file_get_contents($serviceAccountKeyFile), true);
        $header = json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT'
        ]);
        $now = time();
        $payload = json_encode([
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $serviceAccount['private_key'], 'sha256');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
}

if (!function_exists('fcm_getAccessToken')) {
    function fcm_getAccessToken()
    {

        $googleAuthUrl = 'https://oauth2.googleapis.com/token';
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $jwt = fcm_generateJWT();

        $data = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $googleAuthUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        if ($response === FALSE) {
            die('Access Token Request Error: ' . curl_error($ch));
        }

        curl_close($ch);
        $responseData = json_decode($response, true);
        if (isset($responseData['access_token'])) {
            return $responseData['access_token'];
        } else {
            return false;
        }
    }
}

if (!function_exists('send_customer_notification')) {
    function send_customer_notification($title = "", $body = "", $token, $request)
    {
        $accessToken = fcm_getAccessToken();
        //echo $accessToken; die;
        if ($accessToken) {
            $YOUR_PROJECT_ID = 'aadya-64bb1';
            $url = 'https://fcm.googleapis.com/v1/projects/' . $YOUR_PROJECT_ID . '/messages:send';

            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ];

            $message = [
                // 'topic' => 'fcm_mobeen_m',
                'token' => $token,
                'notification' => array(
                    'title' => $title,
                    'body' => $body,
                ),
                'apns' => array(
                    'payload' => [
                        'aps' => [
                            'mutable-content' => 1,
                            'sound' => 'default',
                        ],
                    ],
                ),
                'data' => isset($request->data) ? $request->data : '',
            ];

            $fields = json_encode(['message' => $message]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $response = curl_exec($ch);
            if ($response === FALSE) {
                die('FCM Send Error: ' . curl_error($ch));
            }
        }
    }
}

if (!function_exists('send_customer_notification_notification_test1')) {
    function send_customer_notification_notification_test1($title, $body) {
        $count = 0;
        $accessToken = fcm_getAccessToken();        
        echo $accessToken; die;
    }
}


if (!function_exists('get_price_format')) {
    function get_price_format($price)
    {
        $price_exp = explode('.', $price);
        if (count($price_exp) > 1) {
            $price = $price_exp[0];
            if (strlen($price_exp[1]) == 1) {
                $price .= '.' . $price_exp[1] . "0";
            } else {
                $price .= '.' . substr($price_exp[1], 0, 2);
            }
        } else {
            $price = $price . ".00";
        }
        return $price;
    }
}

function chat_decryptMessage($encryptedText, $key_id='') {
    if ($key_id && $key_id!='') {
        $Chatkeys = Chatkeys::where('id', $key_id)->first();
        if ($Chatkeys) {            
            list($ivHex, $authTagHex, $encryptedHex) = explode(':', $encryptedText);
            $iv = hex2bin($ivHex);
            $authTag = hex2bin($authTagHex);
            $encrypted = hex2bin($encryptedHex);
            $key = hex2bin($Chatkeys->secret_key);
            // Use openssl_decrypt with aes-256-gcm
            $decrypted = openssl_decrypt(
                $encrypted,
                'aes-256-gcm',
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $authTag
            );
            return $decrypted;
        }else{
            return $encryptedText;
        }
    }else{
        return $encryptedText;
    }
}