<?php

declare(strict_types=1);

namespace App\SoapServer\Avantern\Shipment\ServiceType;

use App\SoapServer\Avantern\Shipment\StructType\ShipmentData;
//use SoapFault;
//use WsdlToPhp\PackageBase\AbstractSoapClientBase;

/**
 * This class stands for Save ServiceType
 * @subpackage Services
 */
class Save
{
    /**
     * Method to call the operation originally named saveAvanternShipment
     * Meta information extracted from the WSDL
     * - documentation: saving data
     * @param ShipmentData $parameters
     * @return void|bool
     */
    public function saveAvanternShipment(ShipmentData $data)
    {
        Log::debug('ShipmentData' + json_encode($data));
    }

}
