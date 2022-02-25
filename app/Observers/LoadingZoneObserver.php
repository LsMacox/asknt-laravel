<?php

namespace App\Observers;

use App\Models\LoadingZone;
use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\WialonGeofence;
use WialonResource;

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
        //
    }

    /**
     * Handle the LoadingZone "updated" event.
     *
     * @param  \App\Models\LoadingZone  $loadingZone
     * @return void
     */
    public function updated(LoadingZone $loadingZone)
    {
        $shipment = Shipment::where('stock->id1c', $loadingZone->id_1c)
            ->orWhere('stock->idsap', $loadingZone->id_sap)
            ->first();

        $wResource = WialonResource::useOnlyHosts($shipment->w_conn_id)
                                    ->firstResource()
                                    ->first();

        $wialonGeofence = $loadingZone->wialonGeofences()->first();

        $params = [
            'itemId' => $wResource->id,
            'id' => optional($wialonGeofence)->id ?? 0,
            'callMode' => $wialonGeofence ? 'update' : 'create',
            'w' => $loadingZone->radius ?? 500,
            'f' => 112,
            'n' => $loadingZone->name,
            'd' => 'Геозона создана веб-сервисом',
            't' => 3,
            'c' => 13458524,
            'min' => 1,
            'max' => 19,
            'p' => [
                [
                    'x' => $loadingZone->lng,
                    'y' => $loadingZone->lat,
                    'r' => $loadingZone->radius ?? 500
                ]
            ]
        ];

        $wResult = \Wialon::useOnlyHosts([$shipment->w_conn_id])->resource_update_zone(
            json_encode($params)
        );

        $wialonGeofence = $loadingZone->wialonGeofences()->updateOrCreate(
            ['id' => $wResult[$shipment->w_conn_id][0]],
            ['name' => $loadingZone->name, 'shipment_id' => $shipment->id]
        );
    }

    /**
     * Handle the LoadingZone "deleting" event.
     *
     * @param  \App\Models\LoadingZone  $loadingZone
     * @return void
     */
    public function deleting(LoadingZone $loadingZone)
    {
        //
    }

    /**
     * Handle the LoadingZone "deleted" event.
     *
     * @param  \App\Models\LoadingZone  $loadingZone
     * @return void
     */
    public function deleted(LoadingZone $loadingZone)
    {
        $shipment = Shipment::where('stock->id1c', $loadingZone->id_1c)
            ->orWhere('stock->idsap', $loadingZone->id_sap)
            ->first();

        $wResource = WialonResource::useOnlyHosts($shipment->w_conn_id)
                                    ->firstResource()
                                    ->first();

        $wialonGeofence = $loadingZone->wialonGeofences()->first();

        if ($wialonGeofence) {
            $params = [
                'itemId' => $wResource->id,
                'id' => $wialonGeofence->id,
                'callMode' => 'delete',
            ];

            \Wialon::useOnlyHosts([$shipment->w_conn_id])->resource_update_zone(
                json_encode($params)
            );

            $wialonGeofence->delete();
        }
    }

    /**
     * Handle the LoadingZone "restored" event.
     *
     * @param  \App\Models\LoadingZone  $loadingZone
     * @return void
     */
    public function restored(LoadingZone $loadingZone)
    {
        //
    }

    /**
     * Handle the LoadingZone "force deleted" event.
     *
     * @param  \App\Models\LoadingZone  $loadingZone
     * @return void
     */
    public function forceDeleted(LoadingZone $loadingZone)
    {
        //
    }
}
