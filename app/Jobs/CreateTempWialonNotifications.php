<?php

namespace App\Jobs;

use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\WialonNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateTempWialonNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string|number $hostId
     */
    protected $hostId;

    /**
     * @var object $wResource
     */
    protected object $wResource;

    /**
     * @var Shipment $shipment
     */
    protected Shipment $shipment;

    /**
     * @var object $wObject
     */
    protected object $wObject;

    /**
     * Create a new job instance.
     * @param string|int $hostId
     * @param object $wResource
     * @param Shipment $shipment
     * @param object $wObject
     */
    public function __construct($hostId, object $wResource, Shipment $shipment, object $wObject)
    {
        $this->onQueue('wialon');
        $this->hostId = $hostId;
        $this->wResource = $wResource;
        $this->shipment = $shipment;
        $this->wObject = $wObject;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Wialon::newSession($this->hostId);
        $this->createTempViolationNotification();
        \Wialon::newSession($this->hostId);
        $this->createTempNotification();
    }

    protected function createTempNotification() {
        $params = [
            'itemId' => $this->wResource->w_id,
            'id' => 0,
            'callMode' => 'create',
            'e' => 1,
            'n' => '['.$this->wObject->name.']: '.' Температура',
            'txt' =>
                'unit_id=%UNIT_ID%&sensor_door=%SENSOR(*дверь*)%&mileage=%MILEAGE%&sensor_temp=%SENSOR(*Средняя темп*)%&msg_time=%MSG_TIME%&zone=%ZONE%&zone_min=%ZONE_MIN%&lat=%LAT%&long=%LON%&notification=%NOTIFICATION%',
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
            'un' => [$this->wObject->w_id],
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
                        'url' => route('wialon.temp'),
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
                    'merge' => 0,
                    'prev_msg_diff' => 0,
                    'sensor_name_mask' => '*Средняя темп*',
                    'sensor_type' => 'temperature',
                    'lower_bound' => -100,
                    'upper_bound' => 100,
                ]
            ],
        ];

        $wCreate = \Wialon::useOnlyHosts([$this->hostId])->resource_update_notification(
            json_encode($params)
        );

        $this->shipment->wialonNotifications()->create([
            'id' => $wCreate[$this->hostId][0],
            'w_conn_id' => $this->hostId,
            'name' => $wCreate[$this->hostId][1]->n,
            'action_type' => WialonNotification::ACTION_TEMP,
            'object_id' => $this->wObject->id,
        ]);
    }

    protected function createTempViolationNotification() {
        $params = [
            'itemId' => $this->wResource->w_id,
            'id' => 0,
            'callMode' => 'create',
            'e' => 1,
            'n' => '['.$this->wObject->name.']: Температурное нарушение',
            'txt' =>
                'unit_id=%UNIT_ID%&sensor_temp=%SENSOR(*Средняя темп*)%&msg_time=%MSG_TIME%&lat=%LAT%&long=%LON%&notification=%NOTIFICATION%',
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
            'un' => [$this->wObject->w_id],
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
                    'sensor_name_mask' => '*Средняя темп*',
                    'sensor_type' => 'temperature',
                    'lower_bound' => $this->shipment->temperature['from'],
                    'upper_bound' => $this->shipment->temperature['to'],
                ]
            ],
        ];

        $wCreate = \Wialon::useOnlyHosts([$this->hostId])->resource_update_notification(
            json_encode($params)
        );

        $this->shipment->wialonNotifications()->create([
            'id' => $wCreate[$this->hostId][0],
            'w_conn_id' => $this->hostId,
            'name' => $wCreate[$this->hostId][1]->n,
            'action_type' => WialonNotification::ACTION_TEMP_VIOLATION,
            'object_id' => $this->wObject->w_id,
        ]);
    }

}
