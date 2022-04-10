<?php

namespace App\Models\ShipmentList;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ShipmentShipmentRetailOutlet extends Pivot
{
    protected $table = 'shipment_shipment_retail_outlet';
}
