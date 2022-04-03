<?php

namespace App\Observers;

use App\Models\RetailOutlet;
use App\Models\ShipmentList\ShipmentRetailOutlet;

class ShipmentRetailOutletObserver
{
    /**
     * Handle the Shipment "created" event.
     *
     * @param  \App\Models\ShipmentList\ShipmentRetailOutlet  $shipmentRetailOutlet
     * @return void
     */
    public function created(ShipmentRetailOutlet $shipmentRetailOutlet)
    {
        $data = [
            'name' => $shipmentRetailOutlet->name,
            'code' => $shipmentRetailOutlet->code,
            'address' => $shipmentRetailOutlet->adres,
            'lng' => $shipmentRetailOutlet->long,
            'lat' => $shipmentRetailOutlet->lat,
            'turn' => $shipmentRetailOutlet->turn,
            'radius' => $shipmentRetailOutlet->radius ?? 100
        ];

        RetailOutlet::updateOrCreate(['shipment_retail_outlet_id' => $shipmentRetailOutlet->id], $data);
    }
}
