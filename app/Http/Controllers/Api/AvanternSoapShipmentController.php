<?php

namespace App\Http\Controllers\Api;
use App\Avantern\SoapShipmentServer;
use App\Avantern\Dto\DataDto;
use App\Services\SoapServer\AbstractSoapServerController;
use Laminas\Soap\Wsdl;

class AvanternSoapShipmentController extends AbstractSoapServerController
{
    protected function getService(): string
    {
        return SoapShipmentServer::class;
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
            'SoapDataDto' => DataDto::class,
        ];
    }
}
