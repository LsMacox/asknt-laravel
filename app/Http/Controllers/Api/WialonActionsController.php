<?php


namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Wialon;

class WialonActionsController
{

    public function tempViolation (Request $request) {
        \Log::channel('wialon-actions')->debug('tempViolation: '.json_encode($request->all()));
    }

    public function entranceToGeofence (Request $request) {
        dd($request->getHttpHost());
        $params = [
            'format' => 4,
            'compress' => 1,
            'outputFileName' => '',
        ];

        $wCreate = \Wialon::useOnlyHosts([$host])->resource_update_zone(
            json_encode($params)
        );
        \Log::channel('wialon-actions')->debug('entranceToGeofence: '.json_encode($request->all()));
    }

    public function departureFromGeofence (Request $request) {
        \Log::channel('wialon-actions')->debug('departureFromGeofence: '.json_encode($request->all()));
    }

}
