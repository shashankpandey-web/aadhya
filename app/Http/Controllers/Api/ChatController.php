<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Customers;
use App\Models\Orders;
use App\Models\Logs;
use App\Models\CustomerWallet;
use App\Events\PrivateChat;
use App\Events\MessageSeen;
use App\Events\UnreadMessageCount;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Services\Agora\AgoraService;


class ChatController extends Controller
{
    public function getMessages(Request $request)
    {
        // $Logs                           = new Logs();
        // $Logs->title              = 'getMessages api request';
        // $Logs->data              = json_encode($request->all());
        // $Logs->save();

        $err = [
            'device_id'           => 'required',
            'to'                  => 'required',
            'order_id'                  => 'required',
        ];
        $validation = Validator::make($request->all(), $err);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
            die();
        }


        $Auth        = Auth::guard('api')->user();

        $output = array();
        $output['status'] = false;
        $output['message']      = 'Something went wrong';

        $get_order = Orders::where('id', $request->order_id)->first();
        if ($get_order) {
            // code...
        }else{
            return response()->json($output);
        }

        $customer_wallets = CustomerWallet::where(['order_id' => $get_order->id,'customer_id' => $Auth->id])->orderBy('id', 'desc')->first();

        if ($customer_wallets) {
            // code...
        }else{
            return response()->json($output);
        }

        $diffInSeconds = $customer_wallets->created_at->diffInSeconds(Carbon::now());

        $chat_time = 0;
        $chat_flag = false;
        if ($diffInSeconds<=300) {
            $chat_flag = true;
            $chat_time = 300-$diffInSeconds;
        }

        $previousRecord = CustomerWallet::where('id', '<', $customer_wallets->id)->where(['order_id' => $get_order->id,'customer_id' => $Auth->id])->orderBy('id', 'desc')->first();
        if ($previousRecord) {
            $previousdiffInSeconds = $previousRecord->created_at->diffInSeconds(Carbon::now());
            if ($previousdiffInSeconds<=300) {
                $chat_flag = true;
                $chat_time += 300-$previousdiffInSeconds;
            }
        }

        $data = [];
        $senderId    = $Auth->id;
        $recieverId  = $request->to;
        $timestamp   = $request->timestamp;
        $timeZone    = $request->timeZone;
        $page        = $request->get('page', 1);
        $limit       = 10;
        $offset      = ($page - 1) * $limit;
        $message_arr = [];

        

        // $messagesQuery = Chat::where(function ($query) use ($senderId, $recieverId) {
        //     $query->where('userid', $senderId)
        //         ->where('to', $recieverId);
        // })->orWhere(function ($query) use ($senderId, $recieverId) {
        //     $query->where('userid', $recieverId)
        //         ->where('to', $senderId);
        // }); 
        // $messagesQuery->where('order_id',$request->order_id);
        // $totalMessages = $messagesQuery->count();

        $totalMessages = Chat::where(function ($query) use ($senderId, $recieverId)  {
                $query->where([['userid', '=', $senderId], ['to', '=', $recieverId]])->orWhere([['userid', '=', $recieverId], ['to', '=', $senderId]]);
                })->where('order_id', $request->order_id)->count();  



        // $getChats = Chat::where(function ($query) use ($senderId, $recieverId) {
        //     $query->where('userid', $senderId)
        //         ->where('to', $recieverId);
        // })->orWhere(function ($query) use ($senderId, $recieverId) {
        //     $query->where('userid', $recieverId)
        //         ->where('to', $senderId);
        // });
        // $getChats->where('order_id',$request->order_id);
        // $messages = $getChats->skip($offset)->take($limit)->orderby('chatID', 'desc')->get();

        $messages = Chat::where(function ($query) use ($senderId, $recieverId)  {
                $query->where([['userid', '=', $senderId], ['to', '=', $recieverId]])->orWhere([['userid', '=', $recieverId], ['to', '=', $senderId]]);
                })->where('order_id', $request->order_id)->orderBy('chatID', 'desc')->limit($limit)->offset($offset)->get();        

        foreach ($messages as $key => $value) {
            $message              = [];
            $message['id']        = $value['chatID'];
            //$message['message']   = $value['message'];
            $message['message']   = chat_decryptMessage($value['message'],$value['key_id']);
            $message['userid']    = $value['userid'];
            $message['to']        = $value['to'];
            $message['type']      = $value['type'];
            $message['thumbnail'] = '';

            if ($value['type'] == '1') {
                $message['message'] = $value['message'] ?  url('uploads/chat', $value['message']) : '';
            }

            if ($value['type'] == '3') {
                $message['message'] = $value['message'] ?  url('uploads/chat', $value['message']) : '';
                $message['thumbnail'] = $value['message'] ?  url('uploads/chat/thumbnail', $value['message']) : '';
            }

            if ($value['type'] == '2') {
                $message['message'] = $value['message'] ?  url('uploads/chat', $value['message']) : '';
            }

            $message['date']    = date('Y-m-d h:i:s', $value['CreatedDateTime']);

            $is_seen = false;
            $Chat_messages = Chat::where('chatID', $value['chatID'])->first();
            if ($Chat_messages && $value['to']==$senderId) {
                /*$Chat_messages->is_delivered = 1;
                $Chat_messages->is_seen = 1;
                $Chat_messages->save();*/
                $updated = Chat::where('chatID', $value['chatID'])
                ->where('chatID', $value['chatID'])
                ->update([
                    'is_delivered' => 1,
                    'is_seen' => 1,
                ]);
    
                $is_seen = true;
            }

            $message['is_delivered'] = isset($value['is_delivered']) ? true : false;
            $message['is_seen'] = (isset($is_seen) || isset($value['is_seen'])) ? true : false;


            $message_arr[]      = $message;
        }



        usort($message_arr, function ($a, $b) {
            return $a['id'] - $b['id'];
        });

        $data['messages']  = $message_arr;
        $data['has_more']  = $totalMessages > ($offset + $limit);
        $data['page_count']  = ceil($totalMessages / $limit);

        $data['chat_flag']  = $chat_flag;
        $data['chat_time']  = $chat_time;
        //$data['now_time']  = date('H:i:s');
        $output['status']  = true;
        $output['message'] = 'Message List';
        $output['data']    = $data;

        return response()->json($output);
    }

    public function createAgoraToken(Request $request)
    {
        $err = [
            'device_id' => 'required',
            'channel'   => 'required',
            'user_id'   => 'required',
        ];
        $validation = Validator::make($request->all(), $err);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
            die();
        }

        $userId      = $request->user_id;
        $channelName = $request->channel;

        $token = AgoraService::generateRtcToken($channelName, $userId);
        $data  = ['token' => $token];
        return response()->json(['status' => true, 'message' => 'Token is Created', 'data' => $data]);
    }

    public function sendNotification(Request $request)
    {
        $err = [
            'device_id'  => 'required',
            'to_user_id' => 'required',
            'data'       => 'required',
        ];
        $validation = Validator::make($request->all(), $err);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422);
            die();
        }

        $to_user_id = $request->to_user_id;
        // $to_user_id
        $customer = Customers::where('id', $to_user_id)->whereNull('is_delete')->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }
        if (!$customer->fcm_token) {
            return response()->json(['status' => false, 'message' => 'Fcm Token Not Found'], 200);
        }

        send_customer_notification("", "", $customer->fcm_token, $request);

        return response()->json(['status' => true, 'message' => 'Send Notification Successfully']);
    }

     public function notification_test1(Request $request){
        send_customer_notification_notification_test1('test title','test request description');
        die;
    }
}
