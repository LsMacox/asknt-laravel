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

        $loadingZoneWithoutName = LoadingZone::whereNull(['id_1c', 'id_sap'])->where('name', trim($data['name']))->first();
        $loadingZone = LoadingZone::where('id_1c', $data['id_1c'])->orWhere('id_sap', $data['id_sap'])->first();

        if ($loadingZoneWithoutName) {
            $loadingZoneWithoutName->id_1c = $data['id_1c'];
            $loadingZoneWithoutName->id_sap = $data['id_sap'];
            $loadingZoneWithoutName->save();
            $shipment->loadingZones()->attach($loadingZoneWithoutName);
        } else {
            if ($loadingZone) {
                $shipment->loadingZones()->attach($loadingZone);
            } else {
                LoadingZone::create($data);
            }
        }
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
            $wResource = WialonResource::useOnlyHosts($shipment->w_conn_id)
                                        ->firstResource()
                                        ->first();
            $wialonNotifications = $shipment->wialonNotifications()->get();
            $wialonGeofences = $shipment->wialonGeofences()->get();

            foreach ($wialonNotifications as $notification) {
                $params = [
                    'itemId' => $wResource->id,
                    'id' => $notification->id,
                    'callMode' => 'delete',
                ];

                \Wialon::useOnlyHosts([$shipment->w_conn_id])->resource_update_notification(
                    json_encode($params)
                );

                $notification->delete();
            }

            foreach ($wialonGeofences as $geofence) {
                $params = [
                    'itemId' => $wResource->id,
                    'id' => $geofence->id,
                    'callMode' => 'delete',
                ];

                \Wialon::useOnlyHosts([$shipment->w_conn_id])->resource_update_zone(
                    json_encode($params)
                );

                $geofence->delete();
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
