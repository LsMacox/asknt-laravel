<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// middleware('throttle:login')
    Route::post('/login', [\App\Http\Controllers\Api\Auth\LoginController::class, 'token'])
        ->name('api.login');

Route::prefix('avantern')->group(function () {
    Route::name('avantern.shipment_status.wsdl')->get('/shipment-status.wsdl', function () {
        $wsdl = \Storage::disk('wsdl')->path('avantern/Avantern_ShipmentStatus_Service.wsdl');
        return response($wsdl, 200, config('soap-server.headers.wsdl'));
    });
    Route::name('avantern.shipment.wsdl')->get('/shipment.wsdl', [\App\Http\Controllers\Api\SoapServerAvanternShipmentController::class, 'wsdlProvider']);
    Route::name('avantern.shipment')->post('/shipment', [\App\Http\Controllers\Api\SoapServerAvanternShipmentController::class, 'soapServer']);
});

Route::prefix('wialon')->group(function () {
    Route::get('entrance-to-geofence');
    Route::get('departure-from-geofence');
});

Route::middleware('auth:sanctum')->namespace('Api')->group(function () {
    /* RetailOutletsController */
    Route::get('retail-outlets/list', 'RetailOutletsController@list');
    Route::post('retail-outlets/create', 'RetailOutletsController@create');
    Route::patch('retail-outlets/update/{retail_outlet}', 'RetailOutletsController@update');
    Route::delete('retail-outlets/delete/{retail_outlet}', 'RetailOutletsController@destroy');

    /* LoadingZonesController */
    Route::get('loading-zones/list', 'LoadingZonesController@list');
    Route::post('loading-zones/create', 'LoadingZonesController@create');
    Route::patch('loading-zones/update/{retail_outlet}', 'LoadingZonesController@update');
    Route::delete('loading-zones/delete/{retail_outlet}', 'LoadingZonesController@destroy');

    /* TransportController */
    Route::get('transport/brief-info', 'TransportController@getBriefInfo');
    Route::get('transport/test', 'TransportController@test');

    /* WialonConnectionController */
//    Route::prefix('wialon-connection')->middleware('level:4')->middleware('ability:level:4')->group(function () {
//        Route::post('create', 'WialonConnectionController@create');
//        Route::patch('update', 'WialonConnectionController@update');
//        Route::delete('delete', 'WialonConnectionController@delete');
//        Route::get('list', 'WialonConnectionController@list');
//    });
});
