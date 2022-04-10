<?php

namespace App\Observers;

use App\Jobs\CreateWialonGeofence;
use App\Models\LoadingZone;
use App\Models\LoadingZoneShipment;
use App\Models\Wialon\WialonResources;

class LoadingZoneShipmentObserver
{
    /**
     * Handle the LoadingZoneShipment "created" event.
     *
     * @param  \App\Models\LoadingZoneShipment  $loadingZoneShipment
     * @return void
     */
    public function created(LoadingZoneShipment $loadingZoneShipment)
    {
        $loadingZone = LoadingZone::find($loadingZoneShipment->loading_zone_id);
        $shipment = $loadingZoneShipment->pivotParent;
        $wResource = WialonResources::where('w_conn_id', $shipment->w_conn_id)->first();

        CreateWialonGeofence::dispatch($loadingZone, $wResource, $shipment);
    }
}
