<?php

namespace App\Jobs\InitWialon;

use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\WialonNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class InitWialonNotifications implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const WIALON_NOTIFICATION_NAMES = [
        'вход в геозону',
        'выход из геозоны',
        'температура'
    ];

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
     * @var Shipment $shipment
     */
    protected $shipment;

    /**
     * @var object $wObject
     */
    protected $wObject;

    /**
     * Create a new job instance.
     * @param string|number $hostId
     * @param Collection $retailOutlets
     * @param object $resource
     * @param Shipment $shipment
     * @param object $wObject
     */
    public function __construct($hostId, Collection $retailOutlets, object $resource, Shipment $shipment, object $wObject)
    {
        $this->hostId = $hostId;
        $this->retailOutlets = $retailOutlets;
        $this->resource = $resource;
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
        $wialonGeofences = $this->retailOutlets->load('wialonGeofences')
            ->pluck('wialonGeofences')
            ->map(function ($geofence) {
                return $geofence->first();
            });

        // Creating a temperature violation notification in wialon
        $this->createWialonTempViolationNotification();

        foreach (self::WIALON_NOTIFICATION_NAMES as $name) {
            $params = [
                'itemId' => $this->resource->w_id,
                'id' => 0,
                'callMode' => 'create',
                'e' => 1,
                'n' => '['.$this->wObject->name.']: '.$name,
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
            ];

            $updateNotificationParams = array_merge(
                $params,
                $this->genWialonNotification($name, [
                    'geofences' => $wialonGeofences,
                    'shipment' => $this->shipment,
                ])
            );

            $wCreate = \Wialon::useOnlyHosts([$this->hostId])->resource_update_notification(
                json_encode($updateNotificationParams)
            );

            $this->shipment->wialonNotifications()->create([
                'id' => $wCreate[$this->hostId][0],
                'w_conn_id' => $this->hostId,
                'name' => $wCreate[$this->hostId][1]->n,
                'action_type' => $name === 'температура' ? WialonNotification::ACTION_TEMP : WialonNotification::ACTION_GEOFENCE,
                'object_id' => $this->wObject->id,
            ]);
        }
    }

    protected function createWialonTempViolationNotification() {
        $params = [
            'itemId' => $this->resource->w_id,
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

    /**
     * @param string $name
     * @param array $args
     * @return array
     */
    protected function genWialonNotification (string $name, array $args) {
        switch ($name) {
            case 'вход в геозону':
                return [
                    'act' => [
                        [
                            't' => 'push_messages',
                            'p' => [
                                'url' => route('wialon.entrance-to-geofence'),
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
                        't' => 'geozone',
                        'p' => [
                            'type' => 0,
                            'lo' => 'OR',
                            'geozone_ids' => $args['geofences']->implode('id', ','),
                        ]
                    ],
                ];
            case 'выход из геозоны':
                return [
                    'act' => [
                        [
                            't' => 'push_messages',
                            'p' => [
                                'url' => route('wialon.departure-from-geofence'),
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
                        't' => 'geozone',
                        'p' => [
                            'type' => 1,
                            'lo' => 'OR',
                            'geozone_ids' => $args['geofences']->implode('id', ','),
                        ]
                    ],
                ];
            case 'температура':
                return [
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
//                            'lower_bound' => 1,
                            'merge' => 0,
                            'prev_msg_diff' => 0,
                            'sensor_name_mask' => '*Средняя темп*',
                            'sensor_type' => 'temperature',
                            'upper_bound' => 2,
                        ]
                    ],
                ];
        }
    }

}
