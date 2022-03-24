<?php

namespace App\Observers;

use App\Models\LoadingZone;
use App\Models\ShipmentList\Shipment;
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

        $this->syncWithWialon($shipments, $loadingZone);
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
        $shipments = Shipment::ofStockId($loadingZone->only(['id_sap', 'id_1c']))
                                ->get();
        $wialonGeofences = $loadingZone->wialonGeofences()->get();

        $shipments->each(function ($shipment) use ($wialonGeofences, $loadingZone, $shipments) {
            $wResource = WialonResources::where('w_conn_id', $shipment->w_conn_id)->first();
            $loadingZone->shipments()->detach($shipment);

            foreach ($wialonGeofences as $geofence) {
                $params = [
                    'itemId' => $wResource->w_id,
                    'id' => $geofence->id,
                    'callMode' => 'delete',
                ];

                \Wialon::useOnlyHosts([$shipment->w_conn_id])->resource_update_zone(
                    json_encode($params)
                );

                $geofence->delete();
            }
        });
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

    /**
     * @param $shipments
     * @param LoadingZone $loadingZone
     * @return void
     */
    public function syncWithWialon($shipments, LoadingZone $loadingZone)
    {
        $shipments->each(function ($shipment) use ($loadingZone, $shipments) {
            $wResource = WialonResources::where('w_conn_id', $shipment->w_conn_id)->first();
            $wialonGeofences = $loadingZone->wialonGeofences()->get();

            foreach ($wialonGeofences as $geofence) {
                $params = [
                    'itemId' => $wResource->w_id,
                    'id' => $geofence->id,
                    'callMode' => 'update',
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

                \Wialon::useOnlyHosts([$shipment->w_conn_id])->resource_update_zone(
                    json_encode($params)
                );

                $loadingZone->update(['name' => $loadingZone->name]);
            }
        });
    }
}
