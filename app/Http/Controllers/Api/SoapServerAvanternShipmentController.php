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

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getWsdlUri(): string
    {
        return $this->getDynamicWsdl();
    }

    protected function getEndpoint(): string
    {
        return route('avantern.shipment');
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function wsdlProvider(ResponseFactory $responseFactory)
    {
        return $responseFactory->make(
            $this->getDynamicWsdl(),
            200,
            $this->getWsdlHeaders()
        );
    }

    /**
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getDynamicWsdl (): string
    {
        $wsdl = Storage::disk('wsdl')->get('avantern/Avantern_Shipment_Service.wsdl.blade.php');
        return view(['template' => $wsdl], ['host' => \Request::getHttpHost()])
            ->render();
    }
}
