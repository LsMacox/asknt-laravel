<?php

namespace App\Jobs;

use App\Models\ShipmentList\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use WialonResource;

class InitWialon implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Shipment $shipment
     */
    protected $shipment;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    const WIALON_NOTIFICATION_NAMES = [
        'вход в геозону',
        'выход из геозоны',
    ];

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
        \Log::channel('jobs')->debug('InitWialon['.$this->shipment->id.']: ' . json_encode($this->shipment));

        $shipment = $this->shipment;
        $geofences = $shipment->shipmentRetailOutlets()
            ->whereNotNull(['long', 'lat'])
            ->get();

        $wObjects = WialonResource::getObjectsWithRegPlate();
        $objectHost = $wObjects->search(function ($item) use ($shipment) {
            return $item->contains('registration_plate', \Str::lower($shipment->car))
                || $item->contains('registration_plate', \Str::lower($shipment->trailer));
        });

        if ($objectHost) {
            $wResource = WialonResource::firstResource()[$objectHost];
            $wObject = $wObjects[$objectHost]
                ->filter(function ($item) use ($shipment) {
                    return \Str::is($item->registration_plate, \Str::lower($shipment->car))
                        || \Str::is($item->registration_plate, \Str::lower($shipment->trailer));
                })->first();

            // Creating geofences in wialon
            $this->createWialonGeofences(
                $objectHost,
                $wResource,
                $geofences,
                $shipment,
            );

            // Updating notifications in wialon
            $this->updateWialonNotification(
                $objectHost,
                $wResource,
                $geofences,
                $wObject,
                $shipment,
            );

            // Creating a temperature violation notification in wialon
            $this->createWialonTempViolationNotification(
                $objectHost,
                $wResource,
                $wObject,
                $shipment
            );
        }
    }

    /**
     * @param string $host
     * @param object $resource
     * @param Collection $geofences
     * @param Shipment $shipment
     */
    protected function createWialonGeofences (
        string $host,
        object $resource,
        Collection $geofences,
        Shipment $shipment
    ) {
        foreach ($geofences as $zone) {
            $params = [
                'itemId' => $resource->id,
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

            $wCreate = \Wialon::useOnlyHosts([$host])->resource_update_zone(
                json_encode($params)
            );

            $zone->wialonGeofences()->create([
                'id' => $wCreate[$host][0],
                'name' => $wCreate[$host][1]->n
            ]);
        }
    }

    /**
     * @param string $host
     * @param object $resource
     * @param Collection $geofences
     * @param object $wObject
     * @param Shipment $shipment
     */
    protected function updateWialonNotification (
        string $host,
        object $resource,
        Collection $geofences,
        object $wObject,
        Shipment $shipment
    ) {
        $geofences = $geofences->load('wialonGeofences')
            ->pluck('wialonGeofences')
            ->map(function ($geofence) {
                return $geofence->first();
            });

        foreach (self::WIALON_NOTIFICATION_NAMES as $name) {
            $params = [
                'itemId' => $resource->id,
                'id' => 0,
                'callMode' => 'create',
                'e' => 1,
                'n' => '['.$wObject->nm.']: '.$name,
                'txt' =>
                    'unit_id=%UNIT_ID%&sensor_door=%SENSOR(*дверь*)%&sensor_temp=%SENSOR(*средняя темп*)%&msg_time=%MSG_TIME%&zone=%ZONE%&zones_min=%ZONES_MIN%&lat=%LAT%&long=%LON%&notification=%NOTIFICATION%&stuff_id='
                    .config('wialon.connections.stuff_id'),
                'ta' => 0,
                'td' => 0,
                'ma' => 0,
                'mmtd' => 3600,
                'cdt' => 0,
                'mast' => 0,
                'mpst' => 0,
                'cp' => 3600,
                'fl' => 1,
                'tz' => 3,
                'la' => 'RU',
                'un' => [$wObject->id],
                'sch' => [
                    'f1' => 0,
                    'f2' => 0,
                    't1' => 0,
                    't2' => 0,
                    'm' => 1,
                    'y' => 2,
                    'w' => 2
                ],
            ];

            $updateNotificationParams = array_merge(
                $params,
                $this->genWialonNotification($name, [
                    'geofences' => $geofences,
                    'shipment' => $shipment,
                ])
            );

            $wCreate = \Wialon::useOnlyHosts([$host])->resource_update_notification(
                json_encode($updateNotificationParams)
            );

            $shipment->wialonNotifications()->create([
                'id' => $wCreate[$host][0],
                'name' => $wCreate[$host][1]->n
            ]);

        }
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
                'unit_id=%UNIT_ID%&sensor_temp=%SENSOR(*средняя темп*)%&msg_time=%MSG_TIME%&lat=%LAT%&long=%LON%&notification=%NOTIFICATION%&stuff_id='
                .config('wialon.connections.stuff_id'),
            'ta' => 0,
            'td' => 0,
            'ma' => 0,
            'mmtd' => 3600,
            'cdt' => 0,
            'mast' => 0,
            'mpst' => 0,
            'cp' => 3600,
            'fl' => 1,
            'tz' => 3,
            'la' => 'RU',
            'un' => [$wObject->id],
            'sch' => [
                'f1' => 0,
                'f2' => 0,
                't1' => 0,
                't2' => 0,
                'm' => 1,
                'y' => 2,
                'w' => 2
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
        }
    }
}
