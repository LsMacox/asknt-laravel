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
                'token' => 'a00ffa12ad07a6f094233b7f84349996E674F2940CB037E153C19D55E30A4873C09389EA',
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
