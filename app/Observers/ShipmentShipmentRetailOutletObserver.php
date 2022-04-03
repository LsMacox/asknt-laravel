<?php

namespace App\Observers;

use App\Jobs\CreateWialonGeofence;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use App\Models\ShipmentList\ShipmentShipmentRetailOutlet;
use App\Models\Wialon\WialonResources;

class ShipmentShipmentRetailOutletObserver
{
    /**
     * Handle the ShipmentShipmentRetailOutlet "created" event.
     *
     * @param  \App\Models\ShipmentList\ShipmentShipmentRetailOutlet  $shipmentShipmentRetailOutlet
     * @return void
     */
    public function created(ShipmentShipmentRetailOutlet $shipmentShipmentRetailOutlet)
    {
        $retailOutlet = ShipmentRetailOutlet::find($shipmentShipmentRetailOutlet->shipment_retail_outlet_id);
        $shipment = $shipmentShipmentRetailOutlet->pivotParent;
        $wResource = WialonResources::where('w_conn_id', $shipment->w_conn_id)->first();
        CreateWialonGeofence::dispatch($retailOutlet, $wResource, $shipment)->onQueue('wialon');
    }
}
