<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Digiflazz H2H API Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials for the Digiflazz prepaid top-up provider.
    | Obtain from: https://digiflazz.com/
    |
    */

    'base_url'       => env('DIGIFLAZZ_BASE_URL', 'https://api.digiflazz.com/v1'),
    'username'       => env('DIGIFLAZZ_USERNAME'),
    'api_key'        => env('DIGIFLAZZ_API_KEY'),
    'webhook_secret' => env('DIGIFLAZZ_WEBHOOK_SECRET'),

];
