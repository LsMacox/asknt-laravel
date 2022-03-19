<?php

namespace App\Http\Controllers\Api;

use App\Services\SoapServer\AbstractSoapServerController;
use App\SoapServices\ShipmentSoapService;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Storage;

class SoapServerAvanternShipmentController extends AbstractSoapServerController
{
    public $returnResponse = false;

    protected function getService(): string
    {
        return ShipmentSoapService::class;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getWsdlUri(): string
    {
        $this->compileWsdl();

        if (config('app.debug')) {
            return Storage::disk('wsdl')->path('avantern/Avantern_Shipment_Service.wsdl');
        }

        return route('avantern.shipment.wsdl');
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
            $this->compileWsdl(),
            200,
            $this->getWsdlHeaders()
        );
    }

    /**
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function compileWsdl (): string
    {
        $wsdl = Storage::disk('wsdl')->get('avantern/Avantern_Shipment_Service.wsdl.blade.php');
        $compiledWsdl = view(['template' => $wsdl], ['host' => \Request::getHttpHost()])
                        ->render();
        Storage::disk('wsdl')->put('avantern/Avantern_Shipment_Service.wsdl', $compiledWsdl);
        return $compiledWsdl;
    }
}
