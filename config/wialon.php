<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Wialon connection
    |--------------------------------------------------------------------------
    |
    | The configuration of the setup for connecting to wialon is described here
    |
    */

    'connections' => [
        'stuff_id' => '36fe9b8c34424a68a67a60d1e730704a', // required
        'default' => [
            [
                'id' => 1,
                'scheme' => 'https',
                'host' => 'hst-api.wialon.com',
                'port' => '',
                'token' => env('HOSTING_WIALON_TOKEN', ''),
            ],
            [
                'id' => 2,
                'scheme' => 'https',
                'host' => 'gps.cherkizovo.com',
                'port' => '',
                'token' => env('LOCAL_WIALON_TOKEN', ''),
            ],
        ],
    ],

    // additional parameters
    'extra_params' => [],
];
