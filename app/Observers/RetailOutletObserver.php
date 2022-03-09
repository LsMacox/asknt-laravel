<?php

namespace App\Observers;

use App\Models\RetailOutlet;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use WialonResource;

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
        $shipments = $shipmentRetailOutlet->shipments()->get();
        $wialonGeofence = $shipmentRetailOutlet->wialonGeofences()->first();

        $shipments->each(function ($shipment) use ($shipmentRetailOutlet, $wialonGeofence) {
            $wResource = WialonResource::useOnlyHosts($shipment->w_conn_id)
                ->firstResource()
                ->first();

            $params = [
                'itemId' => $wResource->id,
                'id' => optional($wialonGeofence)->id ?? 0,
                'callMode' => $wialonGeofence ? 'update' : 'create',
                'w' => $retailOutlet->radius ?? 100,
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
                        'y' => $retailOutlet->lng,
                        'r' => $retailOutlet->radius ?? 100
                    ]
                ]
            ];

            $wResult = \Wialon::useOnlyHosts([$shipment->w_conn_id])->resource_update_zone(
                json_encode($params)
            );

            $wialonGeofence = $shipmentRetailOutlet->wialonGeofences()->updateOrCreate(
                ['id' => $wResult[$shipment->w_conn_id][0]],
                ['name' => $retailOutlet->name, 'shipment_id' => $shipment->id, 'w_conn_id' => $shipment->w_conn_id]
            );
        });
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
        $shipments = $shipmentRetailOutlet->shipments()->get();
        $wialonGeofence = $shipmentRetailOutlet->wialonGeofences()->first();

        $shipments->each(function ($shipment) use ($shipmentRetailOutlet, $wialonGeofence) {
            $wResource = WialonResource::useOnlyHosts($shipment->w_conn_id)
                ->firstResource()
                ->first();

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
        });
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
}
