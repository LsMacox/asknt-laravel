<?php

namespace App\Jobs;

use App\Models\Wialon\WialonResources;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateWialonGeofence implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var $zone
     */
    protected $zone;

    /**
     * Create a new job instance.
     * @param $zone
     * @param object $wResource
     */
    public function __construct($zone)
    {
        $this->onQueue('wialon');
        $this->zone = $zone;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $zone = &$this->zone;
        $shipments = $zone->shipments()->get();
        $shipments->each(function ($shipment) use ($zone, $shipments) {
            $wResource = WialonResources::where('w_conn_id', $shipment->w_conn_id)->first();
            $wialonGeofences = $zone->wialonGeofences()->get();

            foreach ($wialonGeofences as $geofence) {
                $params = [
                    'itemId' => $wResource->w_id,
                    'id' => $geofence->id,
                    'callMode' => 'update',
                    'w' => $zone->radius ?? 500,
                    'f' => 112,
                    'n' => $zone->name,
                    'd' => 'Геозона создана веб-сервисом',
                    't' => 3,
                    'c' => 13458524,
                    'min' => 1,
                    'max' => 19,
                    'p' => [
                        [
                            'x' => $zone->lat,
                            'y' => $zone->long,
                            'r' => $zone->radius ?? 500
                        ]
                    ]
                ];

                \Wialon::newSession($shipment->w_conn_id);

                \Wialon::useOnlyHosts([$shipment->w_conn_id])->resource_update_zone(
                    json_encode($params)
                );

                $geofence->update(['name' => $zone->name]);
            }
        });
    }
}
