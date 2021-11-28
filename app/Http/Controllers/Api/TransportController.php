<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laminas\Soap\Client as LaminasClient;
use App;

class TransportController extends Controller
{

    /**
     * Получение краткой информации о транспорте
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getBriefInfo (Request $request)
    {
//        $this->wialonTest();
//        $this->sapTest();
        $this->mySapTest();
    }

    public function mySapTest ()
    {
        $wsdl = file_get_contents(route('avantern.shipment.wsdl'));
        $client = new LaminasClient('https://graphical.weather.gov/xml/DWMLgen/wsdl/ndfdXML.wsdl');
        dd($client->getFunctions());
        $res = $client->saveAvanternShipment();

        dd($res);
    }

//    public function shipmentStatusTest () {
//        $this->soapWrapper->add('ShipmentStatus', function ($service) {
//            $service
//                ->wsdl(storage_path('avantern/Avantern_ShipmentStatus_Service.wsdl'))
//                ->trace(true);
//        });
//
//        // Without classmap
//        $response = $this->soapWrapper->call('ShipmentStatus.SI_Avantern_ShipmentStatus_Async_Out');
//
//        dd($response);
//    }
//
//    public function wialonTest () {
//        $params = array (
//            'spec' => array (
//                'itemsType' => 'avl_unit',
//                'propName' => 'sys_name',
//                'propValueMask' => '*',
//                'sortType' => 'sys_name'
//            ),
//            'force' => 1,
//            'flags' => 1,
//            'from' => 0,
//            'to' => 0
//        );
//
//        $result = \Wialon::core_search_items(json_encode($params));
//        dd(json_decode($result['gps.cherkizovo.com']));
//    }
}
