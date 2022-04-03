<?php

namespace App\Observers;

use App\Jobs\DeleteWialonGeofence;
use App\Jobs\UpdateWialonGeofence;
use App\Models\LoadingZone;
use App\Models\ShipmentList\Shipment;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use App\Models\Wialon\WialonGeofence;
use App\Models\Wialon\WialonResources;

class LoadingZoneObserver
{
    /**
     * Handle the LoadingZone "created" event.
     *
     * @param  \App\Models\LoadingZone  $loadingZone
     * @return void
     */
    public function created(LoadingZone $loadingZone)
    {
        $shipments = Shipment::ofStockId($loadingZone->only(['id_sap', 'id_1c']))
                                ->get();

        if ($shipments->isNotEmpty()) {
            $loadingZone->shipments()->attach($shipments);
        }
    }

    /**
     * Handle the LoadingZone "updated" event.
     *
     * @param  \App\Models\LoadingZone  $loadingZone
     * @return void
     */
    public function updated(LoadingZone $loadingZone)
    {
        $shipments = Shipment::ofStockId($loadingZone->only(['id_sap', 'id_1c']))
                                ->get();

        foreach ($shipments as $shipment) {
            $loadigZone = $shipment->loadingZones()->first();
            UpdateWialonGeofence::dispatch($loadigZone)->onQueue('wialon');
        }
    }

    /**
     * Handle the LoadingZone "deleted" event.
     *
     * @param  \App\Models\LoadingZone  $loadingZone
     * @return void
     */
    public function deleted(LoadingZone $loadingZone)
    {
        $wialonGeofences = $loadingZone->wialonGeofences()->get();

        foreach ($wialonGeofences as $geofence) {
            DeleteWialonGeofence::dispatch($geofence)->onQueue('wialon');
        }
    }
}
