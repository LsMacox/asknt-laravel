<?php

namespace App\Http\Controllers\Api;

use App\Services\SoapServer\AbstractSoapServerController;
use App\SoapServices\ShipmentSoapService;
use Illuminate\Routing\ResponseFactory;

class SoapServerAvanternShipmentController extends AbstractSoapServerController
{
    protected function getService(): string
    {
        return ShipmentSoapService::class;
    }

    protected function getWsdlUri(): string
    {
        return Storage::disk('wsdl')
            ->get('avantern/Avantern_Shipment_Service.wsdl');
//        return route('avantern.shipment.wsdl');2
    }

    protected function getEndpoint(): string
    {
        return route('avantern.shipment');
    }

    public function wsdlProvider(ResponseFactory $responseFactory)
    {
        $path = storage_path('wsdl/avantern/Avantern_Shipment_Service.wsdl');
        return $responseFactory->make(file_get_contents($path), 200, $this->getWsdlHeaders());
    }
}
