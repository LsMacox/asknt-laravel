<?php


namespace App\SoapServices\Struct\ShipmentStatus;

use App\SoapServices\Struct\ShipmentStatus\waybill;

class DT_Shipment_ERP_resp
{
    public $system = '';
    public $waybill = '';

    public function __construct(string $system, waybill $waybill)
    {
        $this->system = $system;
        $this->waybill = $waybill;
    }

}
