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
        'default' => [
            [
                'scheme' => 'https',
                'host' => 'hst-api.wialon.com',
                'port' => '',
                'token' => '752dbc1700b13e297ce789f1faa100e2191CE426B494FF0D00DAA90E9D6CDBBF59CA45C2',
            ],
            [
                'scheme' => 'https',
                'host' => 'gps.cherkizovo.com',
                'port' => '',
                'token' => 'c6aa8d75597de473ae6704fbbd31abe4B74E125CBC2E50BD9D06427C26FB47EB4FF6D944',
            ],
        ],
    ],

    // additional parameters
    'extra_params' => [],


];
