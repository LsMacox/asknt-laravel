<?php

namespace App\Observers;

use App\Models\RetailOutlet;
use App\Models\ShipmentList\ShipmentRetailOutlet;

class ShipmentRetailOutletObserver
{
    /**
     * Handle the Shipment "created" event.
     *
     * @param  \App\Models\ShipmentList\ShipmentRetailOutlet  $shipmentRetailOutlet
     * @return void
     */
    public function created(ShipmentRetailOutlet $shipmentRetailOutlet)
    {
        $this->updateOrCreateRetailOutlet($shipmentRetailOutlet);
    }

    /**
     * Handle the Shipment "updated" event.
     *
     * @param  \App\Models\ShipmentList\ShipmentRetailOutlet  $shipmentRetailOutlet
     * @return void
     */
    public function updated(ShipmentRetailOutlet $shipmentRetailOutlet)
    {
        $this->updateOrCreateRetailOutlet($shipmentRetailOutlet);
    }

    /**
     * Handle the Shipment "deleted" event.
     *
     * @param  \App\Models\ShipmentList\ShipmentRetailOutlet  $shipmentRetailOutlet
     * @return void
     */
    public function deleted(ShipmentRetailOutlet $shipmentRetailOutlet)
    {
        //
    }

    /**
     * Handle the Shipment "restored" event.
     *
     * @param  \App\Models\ShipmentList\ShipmentRetailOutlet  $shipmentRetailOutlet
     * @return void
     */
    public function restored(ShipmentRetailOutlet $shipmentRetailOutlet)
    {
        //
    }

    /**
     * Handle the Shipment "force deleted" event.
     *
     * @param  \App\Models\ShipmentList\ShipmentRetailOutlet  $shipmentRetailOutlet
     * @return void
     */
    public function forceDeleted(ShipmentRetailOutlet $shipmentRetailOutlet)
    {
        //
    }

    /**
     * @param ShipmentRetailOutlet $shipmentRetailOutlet
     */
    protected function updateOrCreateRetailOutlet (ShipmentRetailOutlet $shipmentRetailOutlet) {
        $data = [
            'name' => $shipmentRetailOutlet->name,
            'address' => $shipmentRetailOutlet->adres,
            'lng' => $shipmentRetailOutlet->long,
            'lat' => $shipmentRetailOutlet->lat,
            'radius' => $shipmentRetailOutlet->radius ?? 100
        ];

        $retailOutlet = RetailOutlet::updateOrCreate(['code' => $shipmentRetailOutlet->id], $data);
    }

}
