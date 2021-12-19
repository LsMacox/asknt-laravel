<?php


namespace App\SoapServices\Struct\ShipmentStatus;


use App\SoapServices\Struct\ShipmentStatus\messages;


class waybill
{
    public $number;
    public $timestamp;
    public $processing;
    public $messages;

    public function __construct(
        string $number,
        string $timestamp,
        string $processing,
        messages $messages
    )
    {
        $this->number = $number;
        $this->timestamp = $timestamp;
        $this->processing = $processing;
        $this->messages = $messages;
    }

}
