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
     * @param  \App\Models\ShipmentList\Shipment  $shipment
     * @return void
     */
    public function created(Shipment $shipment)
    {
        $this->updateOrCreateLoadingZone($shipment);
    }

    /**
     * Handle the Shipment 'updated" event.
     *
     * @param  \App\Models\ShipmentList\Shipment  $shipment
     * @return void
     */
    public function updated(Shipment $shipment)
    {
        $this->updateOrCreateLoadingZone($shipment);

        if ($shipment->completed) {
            $wObjects = WialonResource::getObjectsWithRegPlate();
            $objectHost = $wObjects->search(function ($item) use ($shipment) {
                return $item->contains('registration_plate', \Str::lower($shipment->car))
                    || $item->contains('registration_plate', \Str::lower($shipment->trailer));
            });
            $wResource = WialonResource::firstResource()[$objectHost];

            $wialonNotifications = $shipment->wialonNotifications()->get();

            foreach ($wialonNotifications as $notification) {
                $params = [
                    'itemId' => $wResource->id,
                    'id' => $notification->id,
                    'callMode' => 'delete',
                ];

                \Wialon::useOnlyHosts([$objectHost])->resource_update_zone(
                    json_encode($params)
                );

                $notification->delete();
            }
        }
    }

    /**
     * Handle the Shipment "deleted" event.
     *
     * @param  \App\Models\ShipmentList\Shipment  $shipment
     * @return void
     */
    public function deleted(Shipment $shipment)
    {
        //
    }

    /**
     * Handle the Shipment "restored" event.
     *
     * @param  \App\Models\ShipmentList\Shipment  $shipment
     * @return void
     */
    public function restored(Shipment $shipment)
    {
        //
    }

    /**
     * Handle the Shipment "force deleted' event.
     *
     * @param  \App\Models\ShipmentList\Shipment  $shipment
     * @return void
     */
    public function forceDeleted(Shipment $shipment)
    {
        //
    }

    /**
     * @param Shipment $shipment
     */
    protected function updateOrCreateLoadingZone (Shipment $shipment) {
        $data = [];

        if (!empty($shipment->stock['id1c'])) {
            $data['id_1c'] = $shipment->stock['id1c'];
        }
        if (!empty($shipment->stock['idsap'])) {
            $data['id_sap'] = $shipment->stock['idsap'];
        }

        $shipment->loadingZones()->updateOrCreate(['name' => $shipment->stock['name']]);
    }
}
