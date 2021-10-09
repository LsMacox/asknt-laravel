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

    'connection' => [
        'scheme' => env('WIALON_SCHEME','http'),
        'host' => env('WIALON_HOST', 'hst-api.wialon.com'),
        'port' => env('WIALON_PORT',''),
    ],

    // additional parameters
    'extra_params' => [],


];
