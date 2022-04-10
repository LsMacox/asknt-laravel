<?php

namespace App\Observers;

use App\Jobs\DeleteWialonGeofence;
use App\Jobs\UpdateWialonGeofence;
use App\Models\RetailOutlet;
use App\Models\ShipmentList\ShipmentRetailOutlet;

class RetailOutletObserver
{
    /**
     * Handle the RetailOutlet "updated" event.
     *
     * @param  \App\Models\RetailOutlet  $retailOutlet
     * @return void
     */
    public function updated(RetailOutlet $retailOutlet)
    {
        $shipmentRetailOutlet = ShipmentRetailOutlet::find($retailOutlet->shipment_retail_outlet_id);
        UpdateWialonGeofence::dispatch($shipmentRetailOutlet);
    }

    /**
     * Handle the RetailOutlet "deleted" event.
     *
     * @param  \App\Models\RetailOutlet  $retailOutlet
     * @return void
     */
    public function deleted(RetailOutlet $retailOutlet)
    {
        $shipmentRetailOutlet = ShipmentRetailOutlet::find($retailOutlet->shipment_retail_outlet_id);
        $wialonGeofences = $shipmentRetailOutlet->wialonGeofences()->get();

        foreach ($wialonGeofences as $geofence) {
            DeleteWialonGeofence::dispatch($geofence)->onQueue('wialon');
        }
    }
}
