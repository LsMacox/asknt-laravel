<?php

declare(strict_types=1);

namespace App\SoapServices;

use App\Models\ShipmentList\ShipmentOrders;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use App\SoapServices\Struct\ShipmentStatus\DT_Shipment_ERP_resp;
use App\SoapServices\Struct\ShipmentStatus\message;
use App\SoapServices\Struct\ShipmentStatus\messages;
use App\SoapServices\Struct\ShipmentStatus\waybill;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\ShipmentList\Shipment;
use App\Jobs\SendShipmentStatus;
use App\Jobs\InitWialon;


/**
 * Class ShipmentService
 * @package App\SoapServer
 */
class ShipmentSoapService
{

    /**
     * Система откуда пришел маршрутный лист
     * @var string $system
     */
    private $system;

    /**
     * Маршрутный лист
     * @var array $waybill
     */
    private $waybill;

    /**
     * Маршрутный лист (не преобразованный)
     * @var array $waybill
     */
    private $origWaybill;

    /**
     * Method to call the operation originally named saveAvanternShipment
     * Meta information extracted from the WSDL
     * - documentation: saving data
     * @param string $system
     * @param object $waybill
     * @return \stdClass $waybill
     */
    public function saveAvanternShipment (string $system, object $waybill)
    {
        $this->system = $system;
        $this->origWaybill = $waybill;
        $this->waybill = $this->prepareWaybill($waybill);

        \Log::channel('soap-server')->debug('Shipment['.$this->waybill['number'].']: '.json_encode($this->waybill));

        $validator = $this->validate();

        if ($validator->fails()) {
            $this->handleValidatorErrors($validator->errors());
        } else {
            $this->saveWaybill();
        }
    }

    /**
     * @param $errors
     */
    protected function handleValidatorErrors ($errors)
    {
        \Log::channel('soap-server')->debug('Shipment errors['.$this->waybill['number'].']: '.json_encode($errors));

        $messages = [];
        foreach ($errors->all() as $k => $error) {
            $struct_message = new message((string) $k, $error);
            $messages[] = $struct_message;
        }

        SendShipmentStatus::dispatch(
            $this->structShipmentStatus($messages, true)
        );
    }

    /**
     * @return PendingDispatch|void
     */
    protected function saveWaybill ()
    {
        $this->waybill['timestamp'] = $this->rawDateToIso($this->waybill['timestamp']);
        $this->waybill['date'] = $this->rawDateToIso($this->waybill['date']);
        $this->waybill['mark'] = Shipment::markToBoolean($this->waybill['mark']);

        $wObjects = \WialonResource::getObjectsWithRegPlate();
        $hostId = $wObjects->search(function ($host) {
            return $host->contains(function ($item) {
                    return \WialonResource::equalObjectRegPlate($item, $this->waybill['car']);
                })
                || $host->contains(function ($item) {
                    return \WialonResource::equalObjectRegPlate($item, $this->waybill['trailer']);
                });
        });

        if (!$hostId) {
            $errors = [
                new message('1', 'Такая машина или прицеп не существует не в одной системе wialon')
            ];
            return SendShipmentStatus::dispatch(
                $this->structShipmentStatus($errors, true)
            );
        }

        $shipment = Shipment::updateOrCreate(
            ['id' => $this->waybill['number']],
            array_merge($this->waybill, ['w_conn_id' => $hostId])
        );

        $statusCreate = \Str::is(Shipment::STATUS_CREATE, $this->waybill['status']);
        $statusDelete = \Str::is(Shipment::STATUS_DELETE, $this->waybill['status']);

        if (!$statusDelete) {
            foreach ($this->waybill['scores']['score'] as $score) {
                $score['date'] = $this->rawDateToIso($score['date']);
                $shipmentScores = ShipmentRetailOutlet::updateOrCreate(['id' => $score['score'], 'shipment_id' => $shipment->id], $score);

                foreach ($score['orders']['order'] as $order) {
                    $order['return'] = ShipmentOrders::returnToBoolean($order['return']);
                    ShipmentOrders::updateOrCreate(['id' => $order['order'], 'shipment_retail_outlet_id' => $shipmentScores->id], $order);
                }
            }
        }

        if ($statusCreate) {
            Bus::batch([
                new InitWialon($shipment),
                new SendShipmentStatus(
                    $this->structShipmentStatus([])
                )
            ])->dispatch();
        } else {
            SendShipmentStatus::dispatch(
                $this->structShipmentStatus([])
            );
        }
    }

    /**
     * Validate waybill
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validate (): \Illuminate\Contracts\Validation\Validator
    {
        $statusCreate = \Str::is(Shipment::STATUS_CREATE, $this->waybill['status']);

        $number = ['required', 'string'];
        $scoreId = ['required','numeric'];
        $orderId = ['required','numeric'];

        if ($statusCreate) {
            $number[] = 'unique:shipments,id';
            $scoreId[] = 'unique:shipment_retail_outlets,id';
            $orderId[] = 'unique:shipment_orders,id';
        }

        return $validator = Validator::make($this->waybill, [
            'number' => $number,
            'status' => ['required', Rule::in(Shipment::ENUM_STATUS)],
            'timestamp' => 'required',
            'date' => 'required|date_format:Ymd',
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

            'scores.score.*.score' => $scoreId,
            'scores.score.*.name' => 'required|string|max:255',
            'scores.score.*.legal_name' => 'string|max:255',
            'scores.score.*.adres' => 'required|string|max:255',
            'scores.score.*.long' => 'required|regex:/^\s*\d+(\.\d+)?\s*$/',
            'scores.score.*.lat' => 'required|regex:/^\s*\d+(\.\d+)?\s*$/',
            'scores.score.*.date' => 'date_format:Ymd',
            'scores.score.*.arrive_from' => 'string|date_format:H:i',
            'scores.score.*.arrive_to' => 'string|date_format:H:i',
            'scores.score.*.turn' => 'required|numeric|min:0',
            'scores.score.*.orders' => 'required|array',

            'scores.score.*.orders.order.*.order' => $orderId,
            'scores.score.*.orders.order.*.product' => 'required|string|max:255',
            'scores.score.*.orders.order.*.weight' => 'required|regex:/^\s*\d+(\.\d+)?\s*$/',
            'scores.score.*.orders.order.*.return' => ['required', 'string', Rule::in(ShipmentOrders::ENUM_RETURN)],
        ]);
    }

    /**
     * @param array $messages
     * @param bool $error
     * @return DT_Shipment_ERP_resp
     */
    protected function structShipmentStatus (array $messages, bool $error = false): DT_Shipment_ERP_resp
    {
        $struct_messages = new messages($messages);
        $struct_waybill = new waybill(
            $this->waybill['number'],
            $this->origWaybill->timestamp,
            $error ? 'E' : 'S',
            $struct_messages
        );
        return new DT_Shipment_ERP_resp($this->system, $struct_waybill);
    }

    /**
     * prepare waybill for save
     * @param object $waybill
     * @return array
     */
    protected function prepareWaybill (object $waybill): array
    {
        $waybill = $this->objectToArray($waybill);

        $waybill['mark'] = mb_strtolower($waybill['mark']);

        $scores = $waybill['scores']['score'];
        $scores = $this->wrapAssoc($scores);

        $waybill['scores']['score'] = collect($scores)->map(function ($score) {
            $score['long'] = (double) $score['long'];
            $score['lat'] = (double) $score['lat'];
            $orders = $score['orders']['order'];
            $orders = $this->wrapAssoc($orders);

            $score['orders']['order'] = collect($orders)->map(function ($order) {
                $order['weight'] = (double) $order['weight'];
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
    protected function wrapAssoc (array $arr): array
    {
        return Arr::isAssoc($arr) ? [$arr] : $arr;
    }

    /**
     * parse raw string date and convert to iso format
     * @param string $date
     * @return string
     */
    protected function rawDateToIso (string $date): string
    {
        return Carbon::parse($date)->toIso8601String();
    }

    /**
     * deep convert of an object into an array
     * @param object|array $data
     * @return object|array
     */
    protected function objectToArray ($data)
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
