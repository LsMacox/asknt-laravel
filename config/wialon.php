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
                'host' => 'gps.cherkizovo.com',
                'port' => '',
                'token' => 'c6aa8d75597de473ae6704fbbd31abe4B74E125CBC2E50BD9D06427C26FB47EB4FF6D944',
            ],
            [
                'scheme' => 'https',
                'host' => 'hst-api.wialon.com',
                'port' => '',
                'token' => 'a00ffa12ad07a6f094233b7f84349996236619305057031DFF997CA0B3023992E6117822',
            ],
        ],
    ],

    // additional parameters
    'extra_params' => [],


];
