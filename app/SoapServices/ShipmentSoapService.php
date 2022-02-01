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
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\ShipmentList\Shipment;
use Illuminate\Support\Facades\Storage;
use WialonResource;
use App\Jobs\SendShipmentStatus;
use App\Jobs\InitWialon;


/**
 * Class ShipmentService
 * @package App\SoapServer
 */
class ShipmentSoapService
{

    const WIALON_NOTIFICATION_NAMES = [
        'вход в геозону',
        'выход из геозоны',
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
        \Log::channel('soap-server')->debug('Shipment['.$waybill->number.']: '.json_encode($waybill));

        $validator = $this->validate($waybill);

        if ($validator->fails()) {
            $this->handleValidatorErrors($system, $waybill, $validator->errors());
        } else {
            $this->saveWaybill($system, $validator->validated());
        }
    }

    /**
     * @param string $system
     * @param $waybill
     * @param $errors
     */
    protected function handleValidatorErrors (string $system, $waybill, $errors)
    {
        \Log::channel('soap-server')->debug('Shipment errors['.$waybill->number.']: '.json_encode($errors));

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
        $struct_DTShipmentERPResp = new DT_Shipment_ERP_resp($system, $struct_waybill);
        SendShipmentStatus::dispatch($struct_DTShipmentERPResp);
    }

    /**
     * @param string $system
     * @param $waybill
     */
    protected function saveWaybill (string $system, $waybill) {
        $waybill['timestamp'] = $this->rawDateToIso($waybill['timestamp']);
        $waybill['date'] = $this->rawDateToIso($waybill['date']);
        $waybill['mark'] = Shipment::markToBoolean($waybill['mark']);

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

        $struct_waybill = new waybill(
            $waybill['number'],
            now()->format('Y-m-d H:i:s'),
            'S',
            new messages([])
        );
        $struct_DTShipmentERPResp = new DT_Shipment_ERP_resp($system, $struct_waybill);

        Bus::chain([
            new InitWialon($shipment),
            new SendShipmentStatus($struct_DTShipmentERPResp)
        ])->dispatch();
    }

    /**
     * Validate waybill
     * @param $waybill
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validate ($waybill) {
        $waybill = $this->prepareWaybill($waybill);

        return $validator = Validator::make($waybill, [
            'number' => 'required|string|unique:shipments,id',
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

            'scores.score.*.score' => 'required|numeric|unique:shipment_retail_outlets,id',
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

            'scores.score.*.orders.order.*.order' => 'required|numeric|unique:shipment_orders,id',
            'scores.score.*.orders.order.*.product' => 'required|string|max:255',
            'scores.score.*.orders.order.*.weight' => 'required|numeric',
            'scores.score.*.orders.order.*.return' => ['required', 'string', Rule::in(ShipmentOrders::ENUM_RETURN_STR)],
        ]);
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
