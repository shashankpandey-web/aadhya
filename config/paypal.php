<?php
$mode = env('PAYPAL_MODE', 'sandbox');
return [
    'paypal' => [
        'mode' => $mode,
        'client_id' => $mode === 'live' ? env('PAYPAL_LIVE_CLIENT_ID') : env('PAYPAL_CLIENT_ID'),
        'secret' => $mode === 'live' ? env('PAYPAL_LIVE_SECRET') : env('PAYPAL_SECRET'),
    ],
];
