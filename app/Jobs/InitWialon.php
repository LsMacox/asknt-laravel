<?php

namespace App\Jobs;

use App\Jobs\InitWialon\InitWialonGeofences;
use App\Jobs\InitWialon\InitWialonNotifications;
use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\WialonNotification;
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

        $wObjects = \WialonResource::useOnlyHosts($shipment->w_conn_id)->getObjectsWithRegPlate();

        $wResource = \WialonResource::useOnlyHosts($shipment->w_conn_id)
                                    ->firstResource()
                                    ->first();

        $wObject = \WialonResource::getObjectByRegPlate(
            $wObjects[$shipment->w_conn_id],
            [$shipment->car, $shipment->trailer]
        );

        // Creating a temperature violation notification in wialon
        $this->createWialonTempViolationNotification(
            $shipment->w_conn_id,
            $wResource,
            $wObject,
            $shipment
        );

        $this->batch()->add([
            new InitWialonGeofences($shipment->w_conn_id, $retailOutlets, $wResource, $shipment),
            new InitWialonNotifications($shipment->w_conn_id, $retailOutlets, $wResource, $shipment, $wObject)
        ]);
    }

    /**
     * @param string $host
     * @param object $resource
     * @param $wObject
     * @param Shipment $shipment
     */
    protected function createWialonTempViolationNotification (
        string $host,
        object $resource,
               $wObject,
        Shipment $shipment
    ) {
        $params = [
            'itemId' => $resource->id,
            'id' => 0,
            'callMode' => 'create',
            'e' => 1,
            'n' => '['.$wObject->nm.']: Температурное нарушение',
            'txt' =>
                'unit_id=%UNIT_ID%&sensor_temp=%SENSOR(*Средняя темп*)%&msg_time=%MSG_TIME%&lat=%LAT%&long=%LON%&notification=%NOTIFICATION%&stuff_id='
                .config('wialon.connections.stuff_id'),
            'ta' => 0,
            'td' => 0,
            'ma' => 0,
            'mmtd' => 0,
            'cdt' => 0,
            'mast' => 0,
            'mpst' => 0,
            'cp' => 0,
            'fl' => 0,
            'tz' => 3,
            'la' => 'RU',
            'un' => [$wObject->id],
            'sch' => [
                'f1' => 0,
                'f2' => 0,
                't1' => 0,
                't2' => 0,
                'm' => 0,
                'y' => 0,
                'w' => 0
            ],
            'act' => [
                [
                    't' => 'push_messages',
                    'p' => [
                        'url' => route('wialon.temp-violation'),
                        'get' => 0
                    ]
                ],
                [
                    't' => 'event',
                    'p' => [
                        'flags' => '0',
                    ]
                ]
            ],
            'trg' => [
                't' => 'sensor_value',
                'p' => [
                    'merge' => '0',
                    'type' => '1',
                    'sensor_name_mask' => '*средняя темп*',
                    'sensor_type' => 'temperature',
                    'lower_bound' => $shipment->temperature['from'],
                    'upper_bound' => $shipment->temperature['to'],
                ]
            ],
        ];

        $wCreate = \Wialon::useOnlyHosts([$host])->resource_update_notification(
            json_encode($params)
        );

        $shipment->wialonNotifications()->create([
            'id' => $wCreate[$host][0],
            'w_conn_id' => $host,
            'name' => $wCreate[$host][1]->n,
            'action_type' => WialonNotification::ACTION_TEMP_VIOLATION,
            'object_id' => $wObject->id,
        ]);
    }

}
