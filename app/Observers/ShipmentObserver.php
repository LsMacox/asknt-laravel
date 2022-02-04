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
        $data = [
            'name' => $shipment->stock['name'],
            'id_1c' => $shipment->stock['id1c'],
            'id_sap' => $shipment->stock['idsap'],
        ];
        $shipment->loadingZones()->create($data);
    }

    /**
     * Handle the Shipment "updated" event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function updated(Shipment $shipment)
    {
        $data = [
            'name' => $shipment->stock['name'],
            'id_1c' => $shipment->stock['id1c'],
            'id_sap' => $shipment->stock['idsap'],
        ];
        $shipment->loadingZones()->update($data);

        if ($shipment->completed) {
            $wResource = WialonResource::useOnlyHosts($shipment->w_conn_id)
                                        ->firstResource()
                                        ->first();
            $wialonNotifications = $shipment->wialonNotifications()->get();

            foreach ($wialonNotifications as $notification) {
                $params = [
                    'itemId' => $wResource->id,
                    'id' => $notification->id,
                    'callMode' => 'delete',
                ];

                \Wialon::useOnlyHosts([$shipment->w_conn_id])->resource_update_zone(
                    json_encode($params)
                );

                $notification->delete();
            }
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
     * Handle the Shipment "force deleted' event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function forceDeleted(Shipment $shipment)
    {
        //
    }

}
