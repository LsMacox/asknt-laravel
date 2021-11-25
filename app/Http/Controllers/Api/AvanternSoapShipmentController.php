<?php

namespace App\Http\Controllers\Api;
use App\Avantern\SoapShipmentServer;

class AvanternSoapShipmentController extends \KDuma\SoapServer\AbstractSoapServerController
{
    protected function getService(): string
    {
        return SoapShipmentServer::class;
    }

    protected function getEndpoint(): string
    {
        return route('avantern.shipment');
    }

    protected function getWsdlUri(): string
    {
        return route('avantern.shipment.wsdl');
    }

    protected function getClassmap(): array
    {
        return [
        ];
    }
}
