<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials for Midtrans payment gateway.
    | Obtain from: https://dashboard.midtrans.com/
    |
    */

    'server_key'    => env('MIDTRANS_SERVER_KEY'),
    'client_key'    => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    | Snap URL — differs between sandbox and production.
    | Sandbox:    https://app.sandbox.midtrans.com/snap/snap.js
    | Production: https://app.midtrans.com/snap/snap.js
    */
    'snap_url'      => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js'),

    'api_base_url'  => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/v1'
        : 'https://app.sandbox.midtrans.com/snap/v1',

    'status_base_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com/v2'
        : 'https://api.sandbox.midtrans.com/v2',

];
