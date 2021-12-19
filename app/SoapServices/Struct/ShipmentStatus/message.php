<?php


namespace App\SoapServices\Struct\ShipmentStatus;


class message
{

    public $item_no;
    public $text;

    public function __construct(string $item_no, string $text)
    {
        $this->item_no = $item_no;
        $this->text = $text;
    }

}
