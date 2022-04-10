<?php

declare(strict_types=1);

namespace App\SoapServices;

use App\Jobs\CreateGeofenceWialonNotifications;
use App\Jobs\CreateTempWialonNotifications;
use App\Jobs\CreateWialonGeofence;
use App\Jobs\DeleteWialonGeofence;
use App\Jobs\DeleteWialonNotification;
use App\Models\LoadingZone;
use App\Models\ShipmentList\ShipmentOrder;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use App\Models\Wialon\WialonNotification;
use App\Models\Wialon\WialonObjects;
use App\Models\Wialon\WialonResources;
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
     * Wialon ресурс данного маршрутника
     * @var $wResource
     */
    private $wResource;

    /**
     * Wialon объект данного маршрутника
     * @var $wObject
     */
    private $wObject;

    /**
     * Цепочка заданий
     * @var $busChain
     */
    private $busChain;

    /**
     * Method to call the operation originally named saveAvanternShipment
     * Meta information extracted from the WSDL
     * - documentation: saving data
     * @param string $system
     * @param object $waybill
     * @return \stdClass $waybill
     */
    public function saveAvanternShipment(string $system, object $waybill)
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
    protected function handleValidatorErrors($errors)
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
    protected function saveWaybill()
    {
        $this->waybill['timestamp'] = $this->rawDateToIso($this->waybill['timestamp']);
        $this->waybill['date'] = $this->rawDateToIso($this->waybill['date']);
        $this->waybill['mark'] = Shipment::markToBoolean($this->waybill['mark']);

        $objects = WialonObjects::where('registration_plate', \WialonResource::prepareRegPlate($this->waybill['car']))
            ->orWhere('registration_plate', \WialonResource::prepareRegPlate($this->waybill['trailer']))->first();
        $hostId = optional($objects)->w_conn_id;

        if (!$objects) {
            $errors = [
                new message('1', 'Такая машина или прицеп не существует не в одной системе wialon')
            ];
            return SendShipmentStatus::dispatch(
                $this->structShipmentStatus($errors, true)
            );
        }

        \DB::beginTransaction();

        try {
            $shipment = Shipment::updateOrCreate(
                ['id' => $this->waybill['number']],
                array_merge($this->waybill, ['w_conn_id' => $hostId])
            );

            $this->wResource = WialonResources::where('w_conn_id', $shipment->w_conn_id)->first();
            $this->wObject = WialonObjects::where('registration_plate', \WialonResource::prepareRegPlate($shipment->car))
                ->orWhere('registration_plate', \WialonResource::prepareRegPlate($shipment->trailer))->first();

            $statusCreate = \Str::is(Shipment::STATUS_CREATE, $this->waybill['status']);
            $statusUpdate = \Str::is(Shipment::STATUS_UPDATE, $this->waybill['status']);
            $statusDelete = \Str::is(Shipment::STATUS_DELETE, $this->waybill['status']);

            if (!$statusDelete) {
                $this->createLoadingZone($shipment);

                $retailOutlets = collect();

                foreach ($this->waybill['scores']['score'] as $score) {
                    $score['date'] = $this->rawDateToIso($score['date']);

                    $orders = collect();
                    $shipmentRetailOutlet = ShipmentRetailOutlet::where('code', $score['score'])->first();

                    if (!$shipmentRetailOutlet) {
                        $shipmentRetailOutlet = ShipmentRetailOutlet::create(
                            array_merge($score, ['code' => $score['score'], 'w_conn_id' => $hostId])
                        );
                    }

                    $shipmentRetailOutlet->update(['turn' => $score['turn']]);

                    $retailOutlets->push($shipmentRetailOutlet->toArray());

                    foreach ($score['orders']['order'] as $order) {
                        $order['return'] = ShipmentOrder::returnToBoolean($order['return']);
                        $shipmentOrder = ShipmentOrder::updateOrCreate(['code' => $order['order']], $order);

                        $orders->push($shipmentOrder->toArray());
                    }

                    $shipmentRetailOutlet->shipmentOrders()
                        ->where('shipment_id', $shipment->id)
                        ->sync(
                            $orders->mapWithKeys(function ($item) use ($shipment) {
                                return [$item['id'] => ['shipment_id' => $shipment->id]];
                            })->toArray()
                        );
                }

                if ($statusUpdate) {
                    $notificationGeofences = $shipment->wialonNotifications->where('action_type', WialonNotification::ACTION_GEOFENCE);
                    foreach ($notificationGeofences as $notification) {
                        $this->busChain[] = new DeleteWialonNotification($notification);
                    }

                    foreach ($shipment->shipmentRetailOutlets()->get() as $shipmentRetailOutlet) {
                        $wialonGeofences = $shipmentRetailOutlet->wialonGeofences()
                            ->where('shipment_id', $shipment->id)
                            ->get();

                        foreach ($wialonGeofences as $geofence) {
                            $this->busChain[] = new DeleteWialonGeofence($geofence);
                        }
                    }


                    $this->busChain[] = function () use ($shipment) {
                        $shipment->shipmentRetailOutlets()->delete();
                    };

                    $this->busChain[] = function () use ($shipment, $retailOutlets) {
                        $shipment->shipmentRetailOutlets()->sync($retailOutlets->pluck('id'));
                    };

                    $this->chainWialonGeofences($shipment);
                    $this->busChain[] = new CreateGeofenceWialonNotifications($hostId, $this->wResource, $shipment, $this->wObject);
                } else {
                    $shipment->shipmentRetailOutlets()->sync($retailOutlets->pluck('id'));
                    $this->chainWialonGeofences($shipment);
                }
            }
            \DB::commit();

            if ($statusCreate) {
                CreateTempWialonNotifications::dispatch($hostId, $this->wResource, $shipment, $this->wObject);
                $this->busChain[] = new CreateGeofenceWialonNotifications($hostId, $this->wResource, $shipment, $this->wObject);
            }

            Bus::chain($this->busChain)->dispatch();

            SendShipmentStatus::dispatch(
                $this->structShipmentStatus([])
            );
        } catch (\Exception $e) {
            \DB::rollback();

            SendShipmentStatus::dispatch(
                $this->structShipmentStatus([new message('0', $e->getMessage())], true)
            );
        }
    }

    /**
     * @param Shipment $shipment
     * @return void
     */
    protected function chainWialonGeofences(Shipment $shipment) {
        foreach ($shipment->shipmentRetailOutlets()->get() as $shipmentRetailOutlet) {
            $wResource = WialonResources::where('w_conn_id', $shipment->w_conn_id)->first();
            $this->busChain[] = new CreateWialonGeofence($shipmentRetailOutlet, $wResource, $shipment);
        }
    }

    /**
     * Creates a loading zone from the stock field
     * @param Shipment $shipment
     * @return void
     */
    protected function createLoadingZone(Shipment $shipment) {
        $data = [
            'name' => $shipment->stock['name'],
            'id_1c' => $shipment->stock['id1c'],
            'id_sap' => $shipment->stock['idsap'],
        ];

        $loadingZoneWithoutId = LoadingZone::whereNull(['id_1c', 'id_sap'])->where('name', trim($data['name']))->first();
        $loadingZone = LoadingZone::when(
            !empty($data['id_1c']),
            function ($query) use ($data) {
                $query->whereNotNull('id_1c')->where('id_1c', $data['id_1c']);
            },
            function ($query) use ($data) {
                $query->whereNotNull('id_sap')->where('id_sap', $data['id_sap']);
            }
        )->first();

        if ($loadingZoneWithoutId) {
            $loadingZoneWithoutId->id_1c = $data['id_1c'];
            $loadingZoneWithoutId->id_sap = $data['id_sap'];
            $loadingZoneWithoutId->save();
            $shipment->loadingZones()->attach($loadingZoneWithoutId);
        } else {
            if ($loadingZone) {
                $shipment->loadingZones()->syncWithoutDetaching($loadingZone);
            } else {
                $loadingZone = LoadingZone::create($data);
                $shipment->loadingZones()->attach($loadingZone);
            }
        }
    }

    /**
     * Validate waybill
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validate(): \Illuminate\Contracts\Validation\Validator
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
            'car' => 'required_without:trailer|string|max:255',
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
            'scores.score.*.orders' => 'array',

            'scores.score.*.orders.order.*.order' => $orderId,
            'scores.score.*.orders.order.*.product' => 'required|string|max:255',
            'scores.score.*.orders.order.*.weight' => 'required|regex:/^\s*\d+(\.\d+)?\s*$/',
            'scores.score.*.orders.order.*.return' => ['required', 'string', Rule::in(ShipmentOrder::ENUM_RETURN)],
        ]);
    }

    /**
     * @param array $messages
     * @param bool $error
     * @return DT_Shipment_ERP_resp
     */
    protected function structShipmentStatus(array $messages, bool $error = false): DT_Shipment_ERP_resp
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
     * Prepare waybill for save
     * @param object $waybill
     * @return array
     */
    protected function prepareWaybill(object $waybill): array
    {
        $waybill = $this->objectToArray($waybill);

        $waybill['number'] = (string) \Str::of($waybill['number'])->trim();
        $waybill['mark'] = mb_strtolower($waybill['mark']);

        $scores = $waybill['scores']['score'];
        $scores = $this->wrapAssoc($scores);

        $waybill['scores']['score'] = collect($scores)->map(function ($score) {
            $score['score'] = (string) \Str::of($score['score'])->trim();
            $score['long'] = (double) $score['long'];
            $score['lat'] = (double) $score['lat'];

            if (isset($score['orders'])) {
                $orders = $score['orders']['order'];
                $orders = $this->wrapAssoc($orders);

                $score['orders']['order'] = collect($orders)->map(function ($order) {
                    $order['order'] = (string) \Str::of($order['order'])->trim();
                    $order['weight'] = (double) $order['weight'];
                    return $order;
                })->toArray();
            }

            return $score;
        })->toArray();

        return $waybill;
    }

    /**
     * ['key' => 'value', ...] => [0 => ['key' => 'value', ...]]
     * @param array $arr
     * @return array|array[]
     */
    protected function wrapAssoc(array $arr): array
    {
        return Arr::isAssoc($arr) ? [$arr] : $arr;
    }

    /**
     * Parse raw string date and convert to iso format
     * @param string $date
     * @return string
     */
    protected function rawDateToIso(string $date): string
    {
        return Carbon::parse($date)->toIso8601String();
    }

    /**
     * Deep convert of an object into an array
     * @param object|array $data
     * @return object|array
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
