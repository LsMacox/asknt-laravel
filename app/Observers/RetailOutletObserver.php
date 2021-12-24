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
        $shipmentRetailOutlet = ShipmentRetailOutlet::find($retailOutlet->code);
        $shipment = $shipmentRetailOutlet->shipment()->first();

        $wObjects = WialonResource::getObjectsWithRegPlate();
        $objectHost = $wObjects->search(function ($item) use ($shipment) {
            return $item->contains('registration_plate', \Str::lower($shipment->car))
                || $item->contains('registration_plate', \Str::lower($shipment->trailer));
        });
        $wResource = WialonResource::firstResource()[$objectHost];

        $wialonGeofence = $shipmentRetailOutlet->wialonGeofences()->first();

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
                    'x' => $retailOutlet->lng,
                    'y' => $retailOutlet->lat,
                    'r' => $retailOutlet->radius ?? 100
                ]
            ]
        ];

        $wResult = \Wialon::useOnlyHosts([$objectHost])->resource_update_zone(
            json_encode($params)
        );

        $wialonGeofence = $shipmentRetailOutlet->wialonGeofences()->updateOrCreate(
            ['id' => $wResult[$objectHost][0]],
            ['name' => $retailOutlet->name]
        );
    }

    /**
     * Handle the RetailOutlet "deleted" event.
     *
     * @param  \App\Models\RetailOutlet  $retailOutlet
     * @return void
     */
    public function deleted(RetailOutlet $retailOutlet)
    {
        //
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
