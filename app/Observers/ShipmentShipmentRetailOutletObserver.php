<?php

namespace App\Observers;

use App\Jobs\WialonGeofence;
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
        WialonGeofence::dispatch($retailOutlet, $wResource, $shipment);
    }

    /**
     * Handle the ShipmentShipmentRetailOutlet "updated" event.
     *
     * @param  \App\Models\ShipmentList\ShipmentShipmentRetailOutlet  $shipmentShipmentRetailOutlet
     * @return void
     */
    public function updated(ShipmentShipmentRetailOutlet $shipmentShipmentRetailOutlet)
    {
        //
    }

    /**
     * Handle the ShipmentShipmentRetailOutlet "deleted" event.
     *
     * @param  \App\Models\ShipmentList\ShipmentShipmentRetailOutlet  $shipmentShipmentRetailOutlet
     * @return void
     */
    public function deleted(ShipmentShipmentRetailOutlet $shipmentShipmentRetailOutlet)
    {
        //
    }

    /**
     * Handle the ShipmentShipmentRetailOutlet "restored" event.
     *
     * @param  \App\Models\ShipmentList\ShipmentShipmentRetailOutlet  $shipmentShipmentRetailOutlet
     * @return void
     */
    public function restored(ShipmentShipmentRetailOutlet $shipmentShipmentRetailOutlet)
    {
        //
    }

    /**
     * Handle the ShipmentShipmentRetailOutlet "force deleted" event.
     *
     * @param  \App\Models\ShipmentList\ShipmentShipmentRetailOutlet  $shipmentShipmentRetailOutlet
     * @return void
     */
    public function forceDeleted(ShipmentShipmentRetailOutlet $shipmentShipmentRetailOutlet)
    {
        //
    }
}
