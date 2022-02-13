<?php


namespace App\SoapServices\Struct\ShipmentStatus;


use App\SoapServices\Struct\ShipmentStatus\message;


class messages
{

    /**
     * @var \App\SoapServices\Struct\ShipmentStatus\message[]
     */
    public $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }

}
