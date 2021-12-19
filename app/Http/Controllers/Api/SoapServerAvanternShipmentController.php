<?php

namespace App\Http\Controllers\Api;

use App\Services\SoapServer\AbstractSoapServerController;
use App\SoapServices\ShipmentSoapService;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Storage;

class SoapServerAvanternShipmentController extends AbstractSoapServerController
{
    protected function getService(): string
    {
        return ShipmentSoapService::class;
    }

    protected function getWsdlUri(): string
    {
        return Storage::disk('wsdl')
            ->path('avantern/Avantern_Shipment_Service.wsdl');
//        return route('avantern.shipment.wsdl');
    }

    protected function getEndpoint(): string
    {
        return route('avantern.shipment');
    }

    public function wsdlProvider(ResponseFactory $responseFactory)
    {
        return $responseFactory->make(
            Storage::disk('wsdl')->get('avantern/Avantern_Shipment_Service.wsdl'),
            200,
            $this->getWsdlHeaders()
        );
    }
}
