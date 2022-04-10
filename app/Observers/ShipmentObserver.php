<?php

namespace App\Observers;

use App\Jobs\DeleteWialonGeofence;
use App\Jobs\DeleteWialonNotification;
use App\Models\ShipmentList\Shipment;


class ShipmentObserver
{
    /**
     * Handle the Shipment "updated" event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function updated(Shipment $shipment)
    {
        if ($shipment->completed || $shipment->not_completed) {
            $wialonNotifications = $shipment->wialonNotifications()->get();

            foreach ($wialonNotifications as $notification) {
                DeleteWialonNotification::dispatch($notification);
            }

            $wialonGeofences = $shipment->wialonGeofences()->get();

            foreach ($wialonGeofences as $geofence) {
                DeleteWialonGeofence::dispatch($geofence);
            }
        }
    }
}
