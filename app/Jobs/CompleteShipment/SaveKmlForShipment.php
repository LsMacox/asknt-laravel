<?php

namespace App\Jobs\CompleteShipment;

use App\Models\ShipmentList\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveKmlForShipment implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Shipment $shipment
     */
    protected $shipment;

    /**
     * @var object $resource
     */
    protected $resource;

    /**
     * @var string $path
     */
    public $path;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Shipment $shipment, string $path, $resource)
    {
        $this->shipment = $shipment;
        $this->resource = $resource;
        $this->path = $path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $zones = $this->shipment->wialonGeofences()->get()->map(function ($wg) {
            return ['itemId' => $this->resource->id, 'id' => $wg->id];
        });

        $kml = \Wialon::useOnlyHosts([$this->shipment->w_conn_id])->returnRaw()
            ->exchange_export_zones(
                json_encode([
                    'fileName' => 'zones.kml',
                    'compress' => 0,
                    'zones' => $zones
                ])
            );

        \Storage::disk('completed-routes')->put($this->path.'zones.kml', $kml[$this->shipment->w_conn_id]);
    }

}
