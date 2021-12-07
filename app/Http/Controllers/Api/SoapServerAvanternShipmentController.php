<?php

namespace App\Http\Controllers\Api;

use App\Services\SoapServer\AbstractSoapServerController;
use App\SoapServer\Avantern\Shipment\ServiceType\Save as SoapShipmentService;
use Illuminate\Routing\ResponseFactory;
use App\SoapServer\Avantern\Shipment\StructType\ShipmentData;

class SoapServerAvanternShipmentController extends AbstractSoapServerController
{
    protected function getService(): string
    {
        return SoapShipmentService::class;
    }

    protected function getWsdlUri(): string
    {
        return route('avantern.shipment.wsdl');
    }

    protected function getEndpoint(): string
    {
        return route('avantern.shipment');
    }

    protected function getClassmap(): array
    {
        return [
            'tns:shipmentData' => ShipmentData::class,
        ];
    }

    public function wsdlProvider(ResponseFactory $responseFactory)
    {
        $path = storage_path('wsdl/avantern/Avantern_Shipment_Service.wsdl');
        return $responseFactory->make(file_get_contents($path), 200, $this->getWsdlHeaders());
    }
}
