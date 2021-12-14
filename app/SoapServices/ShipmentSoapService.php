<?php

declare(strict_types=1);

namespace App\SoapServices;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\ShipmentList\Shipment;


/**
 * Class ShipmentService
 * @package App\SoapServer
 */
class ShipmentSoapService
{
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

        dd($validator->errors());

        \Log::channel('soap-server')->debug($system.': '.json_encode($waybill));
    }

    protected function handleValidatorErrors () {
        $client = new \Laminas\Soap\Client(route('avantern.shipment_status.wsdl'));
        dd($client->getFunctions());
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
            'status' => ['required', 'string', Rule::in(Shipment::ENUM_STATUS)],
            'timestamp' => 'required|string|max:255',
            'date' => 'required|date',
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
            'scores.score.*.long' => 'required|string|numeric',
            'scores.score.*.lat' => 'required|string|numeric',
            'scores.score.*.date' => 'date',
            'scores.score.*.arrive_from' => 'string|date_format:H:i',
            'scores.score.*.arrive_to' => 'string|date_format:H:i',
            'scores.score.*.turn' => 'required|numeric|min:0',
            'scores.score.*.orders' => 'required|array',

            'scores.score.*.orders.order.*.order' => 'required|numeric',
            'scores.score.*.orders.order.*.product' => 'required|string|max:255',
            'scores.score.*.orders.order.*.weight' => 'required|numeric',
            'scores.score.*.orders.order.*.return' => ['required', 'string', Rule::in(['не возврат', 'возврат'])],
        ]);
    }

    /**
     * prepare waybill for save
     * @param $waybill
     * @return array
     */
    protected function prepareWaybill ($waybill) {
        $waybill = $this->objectToArray($waybill);

        $waybill['status'] = (int) $waybill['status'];
        $waybill['mark'] = mb_strtolower($waybill['mark']);

        $scores = $waybill['scores']['score'];
        $scores = $this->wrapAssoc($scores);

        $waybill['scores']['score'] = collect($scores)->map(function ($score) {
            $score['score'] = (int) $score['score'];
            $score['long'] = (int) $score['long'];
            $score['lat'] = (int) $score['lat'];
            $score['turn'] = (int) $score['turn'];

            $orders = $score['orders']['order'];
            $orders = $this->wrapAssoc($orders);

            $score['orders']['order'] = collect($orders)->map(function ($order) {
                $order['order'] = (int) $order['order'];
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
