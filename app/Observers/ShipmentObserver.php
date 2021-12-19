<?php

namespace App\Observers;

use App\Models\LoadingZone;
use App\Models\ShipmentList\Shipment;


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

        $loadingZone = LoadingZone::updateOrCreate(
            $data,
            ['name' => $shipment->stock['name']]
        );
    }
}
