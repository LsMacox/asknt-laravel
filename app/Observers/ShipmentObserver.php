<?php

namespace App\Observers;

use App\Models\LoadingZone;
use App\Models\ShipmentList\Shipment;
use App\Facades\WialonResource;


class ShipmentObserver
{

    /**
     * Handle the Shipment "created" event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function created(Shipment $shipment)
    {
        //
    }

    /**
     * Handle the Shipment "updated" event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function updated(Shipment $shipment)
    {
        if ($shipment->completed || $shipment->not_completed) {
            $shipment->wialonNotifications()->delete();
            $shipment->wialonGeofences()->delete();
        }
    }

    /**
     * Handle the Shipment "deleted" event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function deleted(Shipment $shipment)
    {
        //
    }

    /**
     * Handle the Shipment "restored" event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function restored(Shipment $shipment)
    {
        //
    }

    /**
     * Handle the Shipment "force deleted" event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function forceDeleted(Shipment $shipment)
    {
        //
    }

}
