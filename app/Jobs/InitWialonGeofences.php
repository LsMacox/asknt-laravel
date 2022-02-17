<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class InitWialonGeofences implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string|number $hostId
     */
    protected $hostId;

    /**
     * @var Collection $retailOutlets
     */
    protected $retailOutlets;

    /**
     * @var object $resource
     */
    protected $resource;

    /**
     * Create a new job instance.
     * @param string|number $hostId
     * @param Collection $retailOutlets
     * @param object $resource
     */
    public function __construct($hostId, Collection $retailOutlets, object $resource)
    {
        $this->hostId = $hostId;
        $this->retailOutlets = $retailOutlets;
        $this->resource = $resource;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->retailOutlets as $zone) {
            $params = [
                'itemId' => $this->resource->id,
                'id' => 0,
                'callMode' => 'create',
                'w' => $zone->radius ?? 100,
                'f' => 112,
                'n' => $zone->name,
                'd' => 'Геозона создана веб-сервисом',
                't' => 3,
                'c' => 13458524,
                'min' => 1,
                'max' => 19,
                'p' => [
                    [
                        'x' => $zone->long ?? $zone->lng,
                        'y' => $zone->lat,
                        'r' => $zone->radius ?? 100
                    ]
                ]
            ];

            $wCreate = \Wialon::useOnlyHosts([$this->hostId])->resource_update_zone(
                json_encode($params)
            );

            $zone->wialonGeofences()->create([
                'id' => $wCreate[$this->hostId][0],
                'w_conn_id' => $this->hostId,
                'name' => $wCreate[$this->hostId][1]->n
            ]);
        }
    }

}
