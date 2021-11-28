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

Route::post('/login', [\App\Http\Controllers\Api\Auth\LoginController::class, 'token'])->name('api.login');

Route::prefix('avantern')->group(function () {
    Route::name('avantern.shipment.wsdl')->get('/shipment.wsdl', [\App\Http\Controllers\Api\AvanternSoapShipmentController::class, 'wsdlProvider']);
    Route::name('avantern.shipment')->post('/shipment', [\App\Http\Controllers\Api\AvanternSoapShipmentController::class, 'soapServer']);
});

Route::middleware('auth:sanctum')->namespace('Api')->group(function () {
    Route::get('transport/brief-info', 'TransportController@getBriefInfo');

//    Route::prefix('wialon-connection')->middleware('level:4')->group(function () {
//        Route::post('create', 'WialonConnectionController@create');
//        Route::patch('update', 'WialonConnectionController@update');
//        Route::delete('delete', 'WialonConnectionController@delete');
//        Route::get('list', 'WialonConnectionController@list');
//    });
});
