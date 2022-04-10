<?php

namespace App\Jobs;

use App\Models\ShipmentList\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateWialonGeofence implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 10;

    /**
     * @var string|int $hostId
     */
    protected $hostId;

    /**
     * @var $zone
     */
    protected $zone;

    /**
     * @var object $wResource
     */
    protected object $wResource;

    /**
     * @var Shipment $shipment
     */
    protected Shipment $shipment;

    /**
     * Create a new job instance.
     * @param $retailOutlet
     * @param object $wResource
     */
    public function __construct($zone, object $wResource, Shipment $shipment)
    {
        $this->onQueue('wialon');
        $this->hostId = $shipment->w_conn_id;
        $this->zone = $zone;
        $this->wResource = $wResource;
        $this->shipment = $shipment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $params = [
            'itemId' => $this->wResource->w_id,
            'id' => 0,
            'callMode' => 'create',
            'w' => $this->zone->radius ?? 100,
            'f' => 112,
            'n' => $this->zone->name,
            'd' => 'Геозона создана веб-сервисом',
            't' => 3,
            'c' => 13458524,
            'min' => 1,
            'max' => 19,
            'p' => [
                [
                    'x' => $this->zone->long ?? $this->zone->lng,
                    'y' => $this->zone->lat,
                    'r' => $this->zone->radius ?? 100
                ]
            ]
        ];

        \Wialon::newSession($this->hostId);

        $wCreate = \Wialon::useOnlyHosts([$this->hostId])->resource_update_zone(
            json_encode($params)
        );

        if (isset($wCreate[$this->hostId][0])) {
            $this->zone->wialonGeofences()->create([
                'id' => $wCreate[$this->hostId][0],
                'shipment_id' => $this->shipment->id,
                'w_conn_id' => $this->hostId,
                'name' => $wCreate[$this->hostId][1]->n
            ]);
        } else {
            $this->release(10);
        }
    }

}
