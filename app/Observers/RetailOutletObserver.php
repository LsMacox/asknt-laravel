<?php

namespace App\Observers;

use App\Models\RetailOutlet;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use App\Models\Wialon\WialonResources;

class RetailOutletObserver
{
    /**
     * Handle the RetailOutlet "created" event.
     *
     * @param  \App\Models\RetailOutlet  $retailOutlet
     * @return void
     */
    public function created(RetailOutlet $retailOutlet)
    {
        //
    }

    /**
     * Handle the RetailOutlet "updated" event.
     *
     * @param  \App\Models\RetailOutlet  $retailOutlet
     * @return void
     */
    public function updated(RetailOutlet $retailOutlet)
    {
        $shipmentRetailOutlet = ShipmentRetailOutlet::find($retailOutlet->shipment_retail_outlet_id);
        $this->syncWithWialon($shipmentRetailOutlet);
    }

    /**
     * Handle the RetailOutlet "deleted" event.
     *
     * @param  \App\Models\RetailOutlet  $retailOutlet
     * @return void
     */
    public function deleted(RetailOutlet $retailOutlet)
    {
        $shipmentRetailOutlet = ShipmentRetailOutlet::find($retailOutlet->shipment_retail_outlet_id);
        $wialonGeofences = $shipmentRetailOutlet->wialonGeofences()->get();
        $wialonGeofences->delete();
    }

    /**
     * Handle the RetailOutlet "restored" event.
     *
     * @param  \App\Models\RetailOutlet  $retailOutlet
     * @return void
     */
    public function restored(RetailOutlet $retailOutlet)
    {
        //
    }

    /**
     * Handle the RetailOutlet "force deleted" event.
     *
     * @param  \App\Models\RetailOutlet  $retailOutlet
     * @return void
     */
    public function forceDeleted(RetailOutlet $retailOutlet)
    {
        //
    }

    /**
     * @param ShipmentRetailOutlet $retailOutlet
     * @return void
     */
    public function syncWithWialon(ShipmentRetailOutlet $retailOutlet)
    {
        $shipments = $retailOutlet->shipments()->get();
        $shipments->each(function ($shipment) use ($retailOutlet, $shipments) {
            $wResource = WialonResources::where('w_conn_id', $shipment->w_conn_id)->first();
            $wialonGeofences = $retailOutlet->wialonGeofences()->get();

            foreach ($wialonGeofences as $geofence) {
                $params = [
                    'itemId' => $wResource->w_id,
                    'id' => $geofence->id,
                    'callMode' => 'update',
                    'w' => $retailOutlet->radius ?? 500,
                    'f' => 112,
                    'n' => $retailOutlet->name,
                    'd' => 'Геозона создана веб-сервисом',
                    't' => 3,
                    'c' => 13458524,
                    'min' => 1,
                    'max' => 19,
                    'p' => [
                        [
                            'x' => $retailOutlet->lat,
                            'y' => $retailOutlet->long,
                            'r' => $retailOutlet->radius ?? 500
                        ]
                    ]
                ];

                \Wialon::useOnlyHosts([$shipment->w_conn_id])->resource_update_zone(
                    json_encode($params)
                );

                $geofence->update(['name' => $retailOutlet->name]);
            }
        });
    }
}
