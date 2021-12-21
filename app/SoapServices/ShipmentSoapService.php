<?php

declare(strict_types=1);

namespace App\SoapServices;

use App\Models\ShipmentList\ShipmentOrders;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use App\Repositories\LoadingZoneRepository;
use App\SoapServices\Struct\ShipmentStatus\DT_Shipment_ERP_resp;
use App\SoapServices\Struct\ShipmentStatus\message;
use App\SoapServices\Struct\ShipmentStatus\messages;
use App\SoapServices\Struct\ShipmentStatus\waybill;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\ShipmentList\Shipment;
use Illuminate\Support\Facades\Storage;


/**
 * Class ShipmentService
 * @package App\SoapServer
 */
class ShipmentSoapService
{

    private $loadingZoneRepository;

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
            $this->wialonCreateGeozones($shipment);
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
    protected function wialonCreateGeozones (Shipment $shipment) {
        $loadingZones  = $this->loadingZoneRepository
            ->withByIdSapOr1c($shipment->stock['idsap'], $shipment->stock['id1c'])
            ->whereNotNull(['lng', 'lat'])
            ->get();
        $retailOutlets = $shipment->retailOutlets()
            ->whereNotNull(['long', 'lat'])
            ->get();

        $geoZones = $loadingZones->merge($retailOutlets)->all();

        foreach ($geoZones as $zone) {

            $wSearchParams = json_encode(array(
                'spec' => [
                    'itemsType' => 'avl_unit',
                    'propName' => '',
                    'propValueMask' => '',
                    'sortType' => '',
                    'propType' => '',
                    'or_logic' => 0
                ],
                'force' => 1,
                'flags' => 8388609,
                'from' => 0,
                'to' => 0,
            ));


            $wObject = \Wialon::core_search_items($wSearchParams);
            $wItems = collect($wObject)
                ->map(function ($item) {
                    return collect(json_decode($item)->items)
                        ->map(function ($item) {
                            $item->registration_plate = collect($item->pflds)
                                ->where('n', 'registration_plate')
                                ->first()
                                ->v;
                            return $item;
                        });
                });

            $kInWhichValue = $wItems->search(function ($item) use ($shipment) {
                return $item->contains('registration_plate', $shipment->car)
                    || $item->contains('registration_plate', $shipment->trailer);
            });

            if ($kInWhichValue) {
                $wGeoZonesParams = json_encode(array(
                    'itemId' => $zone->id,
                    'id' => $zone->id,
                    'callMode' => 'create',
                    'w' => 10,
                    'f' => 32,
                    'n' => $zone->name,
                    'd' => 'Геозона создана веб-сервисом',
                    't' => 3,
                    'c' => '#D50037',
                    'min' => '1',
                    'max' => '19',
                    'p' => [
                        [
                            'x' => $zone->long ?? $zone->lng,
                            'y' => $zone->lat,
                            'r' => $zone->radius ?? 100
                        ]
                    ]
                ));

                $wUpdateNotificationParams = json_encode(array(
					 'id' => 0,
					 'callMode' => 'create',
					 'e' => 1,
					 'n' => 'Уведомление тест 1',
					 'txt' => 'Это тестовое уведомление',
					 'ma' => 0,
					 'fl' => 0,
					 'la' => 'ru',
					 'un' => ['3697'],
					 'trg' => [
                        't' => 'driver',
						'p' => [
                            'url' => 'http://test.com',
                            'get' => 1,
						]
					 ],
					 'act' => [
						[
                            't' => 'alarm',
                            'p' => []
						]
					 ]
                ));


                $wUpdate = \Wialon::useOnlyHosts([$kInWhichValue])->resource_update_zone($wGeoZonesParams);
//                $wUpdate = \Wialon::useOnlyHosts([$kInWhichValue])->resource_update_notification($wUpdateNotificationParams);
                dd($wUpdate);
            }
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
