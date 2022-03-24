<?php

namespace App\Observers;

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

        $params = [
            'itemId' => $wResource->w_id,
            'id' => 0,
            'callMode' =>'create',
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
                    'x' => $loadingZone->lat,
                    'y' => $loadingZone->lng,
                    'r' => $loadingZone->radius ?? 500
                ]
            ]
        ];

        $wResult = \Wialon::useOnlyHosts([$shipment->w_conn_id])->resource_update_zone(
            json_encode($params)
        );

        $loadingZone->wialonGeofences()->create(
            [
                'id' => $wResult[$shipment->w_conn_id][0],
                'name' => $loadingZone->name,
                'w_conn_id' => $shipment->w_conn_id,
                'shipment_id' => $shipment->id
            ]
        );
    }

    /**
     * Handle the LoadingZoneShipment "updated" event.
     *
     * @param  \App\Models\LoadingZoneShipment  $loadingZoneShipment
     * @return void
     */
    public function updated(LoadingZoneShipment $loadingZoneShipment)
    {
        //
    }

    /**
     * Handle the LoadingZoneShipment "deleted" event.
     *
     * @param  \App\Models\LoadingZoneShipment  $loadingZoneShipment
     * @return void
     */
    public function deleted(LoadingZoneShipment $loadingZoneShipment)
    {
        //
    }

    /**
     * Handle the LoadingZoneShipment "restored" event.
     *
     * @param  \App\Models\LoadingZoneShipment  $loadingZoneShipment
     * @return void
     */
    public function restored(LoadingZoneShipment $loadingZoneShipment)
    {
        //
    }

    /**
     * Handle the LoadingZoneShipment "force deleted" event.
     *
     * @param  \App\Models\LoadingZoneShipment  $loadingZoneShipment
     * @return void
     */
    public function forceDeleted(LoadingZoneShipment $loadingZoneShipment)
    {
        //
    }
}
