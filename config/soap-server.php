<?php

return [
    'wsdl_cache_enabled' => env('WSDL_CACHE_ENABLED', false),

    'wsdl_formatting_enabled' => env('WSDL_FORMATTING_ENABLED', false),

    'headers' => [
        'soap' => [
            'Content-Type' => 'application/soap+xml; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store'
        ],
        'wsdl' => [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store'
        ],
    ],

    'wsdl' => [
        'shipment-status' => [
            'url' => 'http://mow03-piq01tl.cherkizovsky.net:50000/dir/wsdl?p=ic/4f796a90f909319ca7911b253838ad03',
            'username' => env('SHIPMENT_STATUS_LOGIN', 'Avanterna'),
            'password' => env('SHIPMENT_STATUS_PASSWORD', 'PIQ123456')
        ],
    ],
];
