<?php


namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;

class WialonActionsController
{

    public function doorAction (Request $request) {
        \Log::channel('wialon-actions')->debug('doorAction: '.json_encode($request->all()));
    }

    public function tempViolation (Request $request) {
        \Log::channel('wialon-actions')->debug('tempViolation: '.json_encode($request->all()));
    }

    public function entranceToGeofence (Request $request) {
        \Log::channel('wialon-actions')->debug('entranceToGeofence: '.json_encode($request->all()));
    }

    public function departureFromGeofence (Request $request) {
        \Log::channel('wialon-actions')->debug('departureFromGeofence: '.json_encode($request->all()));
    }

}
