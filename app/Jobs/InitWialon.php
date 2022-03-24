<?php

namespace App\Jobs;

use App\Jobs\InitWialonNotifications;
use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\WialonObjects;
use App\Models\Wialon\WialonResources;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InitWialon implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Shipment $shipment
     */
    protected Shipment $shipment;

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

        $retailOutlets = $this->shipment->shipmentRetailOutlets()
            ->whereNotNull(['long', 'lat'])
            ->get();

        $wResource = WialonResources::where('w_conn_id', $this->shipment->w_conn_id)->first();

        $wObject = WialonObjects::where('registration_plate', \WialonResource::prepareRegPlate($this->shipment->car))
            ->orWhere('registration_plate', \WialonResource::prepareRegPlate($this->shipment->trailer))->first();

        $this->batch()->add([
            new InitWialonNotifications($this->shipment->w_conn_id, $retailOutlets, $wResource, $this->shipment, $wObject)
        ]);
    }

}
