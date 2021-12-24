<?php

declare(strict_types=1);

namespace App\SoapServices;

use App\Models\ShipmentList\ShipmentOrders;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use App\Models\Wialon\WialonGeofence;
use App\Repositories\LoadingZoneRepository;
use App\SoapServices\Struct\ShipmentStatus\DT_Shipment_ERP_resp;
use App\SoapServices\Struct\ShipmentStatus\message;
use App\SoapServices\Struct\ShipmentStatus\messages;
use App\SoapServices\Struct\ShipmentStatus\waybill;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\ShipmentList\Shipment;
use Illuminate\Support\Facades\Storage;
use WialonResource;


/**
 * Class ShipmentService
 * @package App\SoapServer
 */
class ShipmentSoapService
{

    const WIALON_NOTIFICATION_NAMES = [
        'вход в геозону',
        'выход из геозоны',
        'дверь'
    ];

    /**
     * @var \Illuminate\Contracts\Foundation\Application|mixed
     */
    private $loadingZoneRepository;

    /**
     * ShipmentSoapService constructor.
     */
    public function __construct()
    {
        $this->loadingZoneRepository = app(LoadingZoneRepository::class);
    }

    /**
     * Method to call the operation originally named saveAvanternShipment
     * Meta information extracted from the WSDL
     * - documentation: saving data
     * @param string $system
     * @return \stdClass $waybill
     */
    public function saveAvanternShipment(string $system, $waybill)
    {
        $validator = $this->validate($waybill);

        if ($validator->fails()) {
            $this->handleValidatorErrors($system, $waybill, $validator->errors());
        } else {
            $this->saveWaybillInDB($system, $validator->validated());
        }

        \Log::channel('soap-server')->debug($system.': '.json_encode($waybill));
    }

    /**
     * @param string $system
     * @param $waybill
     * @param $errors
     */
    protected function handleValidatorErrors (string $system, $waybill, $errors)
    {
        $messages = [];
        foreach ($errors->all() as $k => $error) {
            $struct_message = new message((string) $k, $error);
            array_push($messages, $struct_message);
        }

        $struct_messages = new messages($messages);
        $struct_waybill = new waybill(
            $waybill->number,
            now()->format('Y-m-d H:i:s'),
            'E',
            $struct_messages
        );
        $struct_DTShipmentERPresp = new DT_Shipment_ERP_resp($system, $struct_waybill);
        $this->sendShipmentStatus($struct_DTShipmentERPresp);
    }

    /**
     * @param string $system
     * @param $waybill
     */
    protected function saveWaybillInDB (string $system, $waybill) {
        $waybill['timestamp'] = $this->rawDateToIso($waybill['timestamp']);
        $waybill['date'] = $this->rawDateToIso($waybill['date']);
        $waybill['mark'] = Shipment::markToBoolean($waybill['mark']);

        $isFirstCreate = !Shipment::where('id', $waybill['number'])->exists();
        $shipment = Shipment::updateOrCreate(['id' => $waybill['number']], $waybill);
        $statusDelete = \Str::is(Shipment::STATUS_DELETE, $waybill['status']);

        if (!$statusDelete) {
            foreach ($waybill['scores']['score'] as $score) {
                $score['date'] = $this->rawDateToIso($score['date']);
                $shipmentScores = ShipmentRetailOutlet::updateOrCreate(['id' => $score['score'], 'shipment_id' => $shipment->id], $score);

                foreach ($score['orders']['order'] as $order) {
                    $order['return'] = ShipmentOrders::returnToBoolean($order['return']);
                    ShipmentOrders::updateOrCreate(['id' => $order['order'], 'shipment_retail_outlet_id' => $shipmentScores->id], $order);
                }
            }
        }

        if ($isFirstCreate) {
            $this->initWialon($shipment);
        }

        $struct_waybill = new waybill(
            $waybill['number'],
            now()->format('Y-m-d H:i:s'),
            'S',
            new messages([])
        );
        $struct_DTShipmentERPresp = new DT_Shipment_ERP_resp($system, $struct_waybill);
        $this->sendShipmentStatus($struct_DTShipmentERPresp);
    }

    /**
     * Validate waybill
     * @param $waybill
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validate ($waybill) {
        $waybill = $this->prepareWaybill($waybill);

        return $validator = Validator::make($waybill, [
            'number' => 'required|string',
            'status' => ['required', Rule::in(Shipment::ENUM_STATUS)],
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'date' => 'required|date_format:Y-m-d H:i:s',
            'time' => 'required|string|date_format:H:i',
            'carrier' => 'string|max:255',
            'car' => 'required|string|max:255',
            'trailer' => 'string|max:255',
            'weight' => 'string|max:255',
            'mark' => ['required', 'string', Rule::in(Shipment::ENUM_MARK_STR)],
            'driver' => 'string|max:255',
            'phone' => 'string|max:255',

            'temperature' => 'required|array',
            'stock' => 'required|array',
            'scores' => 'required|array',

            'temperature.from' => 'required|string|max:4',
            'temperature.to' => 'required|string|max:4',

            'stock.name' => 'required|string|max:255',
            'stock.id1c' => 'prohibited_unless:stock.idsap,|string|max:255',
            'stock.idsap' => 'prohibited_unless:stock.id1c,|string|max:255',
            'stock.time' => 'string|date_format:H:i',

            'scores.score.*.score' => 'required|numeric',
            'scores.score.*.name' => 'required|string|max:255',
            'scores.score.*.legal_name' => 'string|max:255',
            'scores.score.*.adres' => 'required|string|max:255',
            'scores.score.*.long' => 'required|numeric',
            'scores.score.*.lat' => 'required|numeric',
            'scores.score.*.date' => 'date_format:Y-m-d H:i:s',
            'scores.score.*.arrive_from' => 'string|date_format:H:i',
            'scores.score.*.arrive_to' => 'string|date_format:H:i',
            'scores.score.*.turn' => 'required|numeric|min:0',
            'scores.score.*.orders' => 'required|array',

            'scores.score.*.orders.order.*.order' => 'required|numeric',
            'scores.score.*.orders.order.*.product' => 'required|string|max:255',
            'scores.score.*.orders.order.*.weight' => 'required|numeric',
            'scores.score.*.orders.order.*.return' => ['required', 'string', Rule::in(ShipmentOrders::ENUM_RETURN_STR)],
        ]);
    }

    /**
     * @param Shipment $shipment
     */
    protected function initWialon (Shipment $shipment) {
        $loadingZones = $this->loadingZoneRepository
            ->builderByIdSapOr1c($shipment->stock['idsap'], $shipment->stock['id1c'])
            ->whereNotNull(['lng', 'lat'])
            ->get();
        $retailOutlets = $shipment->retailOutlets()
            ->whereNotNull(['long', 'lat'])
            ->get();

        $geofences = $loadingZones->merge($retailOutlets);

        $wObjects = WialonResource::getObjectsWithRegPlate();
        $objectHost = $wObjects->search(function ($item) use ($shipment) {
            return $item->contains('registration_plate', \Str::lower($shipment->car))
                || $item->contains('registration_plate', \Str::lower($shipment->trailer));
        });
        $wResource = WialonResource::firstResource()[$objectHost];

        if ($objectHost) {
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
     * @param DT_Shipment_ERP_resp $DT_Shipment_ERP_resp
     */
    protected function sendShipmentStatus(DT_Shipment_ERP_resp $DT_Shipment_ERP_resp) {
        $login = config('soap-server.wsdl.shipment-status.username');
        $password = config('soap-server.wsdl.shipment-status.password');

//        $wsdl = route('avantern.shipment_status.wsdl');
        $wsdl = Storage::disk('wsdl')->path('avantern/Avantern_ShipmentStatus_Service.wsdl');

        $client = new \Laminas\Soap\Client($wsdl, ['login' => $login, 'password' => $password]);
        $client->SI_ShipmentStatus_Avantern_Async_Out($DT_Shipment_ERP_resp);
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
            $notification = collect($resource->unf)->where('n', $name)->first();

            $params = [
                'itemId' => $resource->id,
                'id' => optional($notification)->id ?? 0,
                'callMode' => $notification ? 'update' : 'create',
                'e' => 1,
                'n' => $name,
                'txt' => 'zone=%ZONE%&zones_all=%ZONES_MIN%&date=%CURR_TIME%&location=%LOCATION%&unit=%UNIT%&unit_id=%UNIT_ID%',
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

            $wUpdate = \Wialon::useOnlyHosts([$host])->resource_update_notification(
                json_encode($updateNotificationParams)
            );

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
            'txt' => 'unit=%UNIT%&unit_id=%UNIT_ID%&sensor=%SENSOR(*)%&date=%CURR_TIME%',
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
            case 'дверь':
                return [
                    'act' => [
                        [
                            't' => 'push_messages',
                            'p' => [
                                'url' => route('wialon.door-action'),
                                'get' => 0
                            ]
                        ]
                    ],
                    'trg' => [
                        't' => 'sensor_value',
                        'p' => [
                            'merge' => '0',
                            'sensor_name_mask' => '*дверь*',
                            'sensor_type' => 'any',
                        ]
                    ],
                ];
        }
    }

    /**
     * prepare waybill for save
     * @param $waybill
     * @return array
     */
    protected function prepareWaybill ($waybill) {
        $waybill = $this->objectToArray($waybill);

        $waybill['mark'] = mb_strtolower($waybill['mark']);

        $scores = $waybill['scores']['score'];
        $scores = $this->wrapAssoc($scores);

        $waybill['scores']['score'] = collect($scores)->map(function ($score) {

            $orders = $score['orders']['order'];
            $orders = $this->wrapAssoc($orders);

            $score['orders']['order'] = collect($orders)->map(function ($order) {
                $order['return'] = mb_strtolower($order['return']);
                return $order;
            })->toArray();
            return $score;
        })->toArray();

        return $waybill;
    }

    /**
     * ['key' => 'value', ...] => [0 => ['key' => 'value', ...]]
     * @param array $arr
     * @return array|array[]
     */
    protected function wrapAssoc (array $arr) {
        return Arr::isAssoc($arr) ? [$arr] : $arr;
    }

    /**
     * parse raw string date and convert to iso format
     * @param string $date
     * @return string
     */
    protected function rawDateToIso (string $date) {
        return Carbon::parse($date)->toIso8601String();
    }

    /**
     * deep convert of an object into an array
     * @param $data
     * @return array
     */
    protected function objectToArray($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = [];
            foreach ($data as $key => $value)
            {
                $result[$key] = (is_array($data) || is_object($data)) ? $this->objectToArray($value) : $value;
            }
            return $result;
        }
        return $data;
    }

}
