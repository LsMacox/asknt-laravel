<?php

namespace App\Jobs;

use App\Jobs\InitWialon\InitWialonGeofences;
use App\Jobs\InitWialon\InitWialonNotifications;
use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\WialonNotification;
use App\Models\Wialon\WialonObjects;
use App\Models\Wialon\WialonResources;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InitWialon implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Shipment $shipment
     */
    protected $shipment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $shipment = $this->shipment;
        $retailOutlets = $shipment->shipmentRetailOutlets()
            ->whereNotNull(['long', 'lat'])
            ->get();

        $wResource = WialonResources::where('w_conn_id', $shipment->w_conn_id)->first();

        $wObject = WialonObjects::where('registration_plate', \WialonResource::prepareRegPlate($shipment->car))
            ->orWhere('registration_plate', \WialonResource::prepareRegPlate($shipment->trailer))->first();

        $this->batch()->add([
            new InitWialonGeofences($shipment->w_conn_id, $retailOutlets, $wResource, $shipment),
            new InitWialonNotifications($shipment->w_conn_id, $retailOutlets, $wResource, $shipment, $wObject)
        ]);
    }

}
