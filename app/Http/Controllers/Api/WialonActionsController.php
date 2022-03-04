<?php


namespace App\Http\Controllers\Api;


use App\Jobs\CompleteShipment\GenReportsForShipment;
use App\Jobs\CompleteShipment\SaveKmlForShipment;
use App\Jobs\CompleteShipment\SaveWlnForShipment;
use App\Models\LoadingZone;
use App\Models\RetailOutlet;
use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\Action\ActionWialonGeofence;
use App\Models\Wialon\Action\ActionWialonTempViolation;
use Illuminate\Http\Request;
use App\Models\Wialon\WialonNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;

class WialonActionsController
{

    /**
     * @param Request $request
     * @return void
     */
    public function tempViolation (Request $request) {
        \Log::channel('wialon-actions')->debug('tempViolation: '.json_encode($request->all()));

        $data = $this->normalizeRequest($request);

        $notification = WialonNotification::where('name', $request->notification)->firstOrFail();
        $shipment = $notification->shipment()->first();
        $temperature = $shipment->temperature;

        $notification->actionTempViolations()->create([
            'temp' => $data['sensor_temp'],
            'temp_type' => $data['sensor_temp_type'],
            'lat' => $data['lat'],
            'long' => $data['long'],
            'created_at' => $data['msg_time'],
        ]);

        $shipment->violations()->create([
            'name' => 'Нарушение температуры',
            'text' => 'Температура '. $data['sensor_temp'].', норма от '.$temperature['from'].' до '.$temperature['to'],
            'created_at' => $data['msg_time'],
        ]);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function temp (Request $request) {
        \Log::channel('wialon-actions')->debug('temp: '.json_encode($request->all()));

        $data = $this->normalizeRequest($request);

        $notification = WialonNotification::where('name', $request->notification)->firstOrFail();

        $notification->actionTemps()->create([
            'temp' => $data['sensor_temp'],
            'temp_type' => $data['sensor_temp_type'],
            'created_at' => $data['msg_time'],
        ]);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function entranceToGeofence (Request $request) {
        \Log::channel('wialon-actions')->debug('entranceToGeofence: '.json_encode($request->all()));

        $data = $this->normalizeRequest($request);

        $notification = WialonNotification::where('name', $request->notification)->firstOrFail();
        $shipment = $notification->shipment()->first();

        $point = $this->getPoint($notification, $shipment);

        $actGeofence = $point->actionWialonGeofences()->create([
            'wialon_notification_id' => $notification->id,
            'shipment_id' => $shipment->id,
            'name' => $data['zone'],
            'temp' => $data['sensor_temp'],
            'temp_type' => $data['sensor_temp_type'],
            'door' => $data['sensor_door_type'],
            'lat' => $data['lat'],
            'long' => $data['long'],
            'is_entrance' => true,
            'created_at' => $data['msg_time'],
            'mileage' => $data['mileage'],
        ]);


        if ($point instanceof RetailOutlet) {
            // Create violation
            $shipmentRetailOutlet = $point->shipmentRetailOutlet()->first();

            if ($shipmentRetailOutlet) {
                $actualFinish = Carbon::parse($actGeofence->created_at);

                $planStart = $shipmentRetailOutlet->planStart;
                $planFinish = $shipmentRetailOutlet->planFinish;

                $late = !($actualFinish->gt($planStart) && $actualFinish->lt($planFinish));

                if ($late) {
                    $shipment->violations()->create([
                        'name' => 'Опоздание на ТТ',
                        'text' => 'Прибытие в '.$actualFinish->format('H:i').', норма  '.$planStart->format('H:i').'-'.$planFinish->format('H:i'),
                        'created_at' => $data['msg_time'],
                    ]);
                }
            }
        }
    }

    /**
     * @param Request $request
     * @return void
     */
    public function departureFromGeofence (Request $request) {
        \Log::channel('wialon-actions')->debug('departureFromGeofence: '.json_encode($request->all()));

        $data = $this->normalizeRequest($request);

        $notification = WialonNotification::where('name', $request->notification)->firstOrFail();
        $shipment = $notification->shipment()->first();

        $point = $this->getPoint($notification, $shipment);

        $point->actionWialonGeofences()->create([
            'wialon_notification_id' => $notification->id,
            'shipment_id' => $shipment->id,
            'name' => $data['zone'],
            'temp' => $data['sensor_temp'],
            'temp_type' => $data['sensor_temp_type'],
            'door' => $data['sensor_door_type'],
            'lat' => $data['lat'],
            'long' => $data['long'],
            'is_entrance' => false,
            'created_at' => $data['msg_time'],
            'mileage' => $data['mileage'],
        ]);

        // Completed shipment, if the entrance to the last point
        $lastRetailOutlet = $shipment->retailOutlets()->get()->sortBy('turn')->last();

        if ($point->id === $lastRetailOutlet->id) {
            $actionGeofences = $notification->actionGeofences()->orderBy('created_at')->get();

            $resource = \WialonResource::useOnlyHosts($shipment->w_conn_id)
                ->firstResource()
                ->first();

            $path = $shipment->date->format('d.m.Y').'/'.$shipment->id.'/';

            Bus::batch([
                new SaveWlnForShipment($shipment, $path),
                new SaveKmlForShipment($shipment, $path, $resource),
                new GenReportsForShipment($shipment, $path, $notification, $resource, $actionGeofences),
//                    new SaveWlpForShipment($shipment, $path)
            ])->dispatch();

            $shipment->update(['completed' => true]);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function normalizeRequest (Request $request) {
        $data = $request->all();

        if ($request->has('sensor_temp')) {
            $data['sensor_temp'] = \Str::of($data['sensor_temp'])
                ->remove('Средняя температура iQF:');

            $data['sensor_temp_type'] = ActionWialonTempViolation::getTempType($data['sensor_temp']);

            $data['sensor_temp'] = (string) $data['sensor_temp']
                ->replaceMatches('/('.implode('|',ActionWialonTempViolation::ENUM_TEMP).')/', '')
                ->trim();
        }

        if ($request->has('msg_time')) {
            $data['msg_time'] = Carbon::parse($data['msg_time'])->toIso8601String();
        }

        if ($request->has('mileage')) {
            $data['mileage'] = (string) \Str::of($data['mileage'])
                ->replaceMatches('/(км|km)/', '')
                ->trim();
        }

        if ($request->has('lat')) {
            $data['lat'] = (string) \Str::of($data['lat'])->match('/\d+\.\d*/', '')->trim();
        }

        if ($request->has('long')) {
            $data['long'] = (string) \Str::of($data['long'])->match('/\d+\.\d*/', '')->trim();
        }

        if ($request->has('sensor_door')) {
            $data['sensor_door_type'] = (string) \Str::of($data['sensor_door'])
                ->lower()
                ->match('/('.implode('|',ActionWialonGeofence::ENUM_DOOR).')/i', '')
                ->trim();
        }

        return $data;
    }

    /**
     * @param WialonNotification $notification
     * @param Shipment $shipment
     * @return mixed
     */
    public function getPoint(WialonNotification $notification, Shipment $shipment)
    {
        $loadingCount = $shipment->actionGeofences()
            ->where('pointable_type', LoadingZone::getMorphClass())
            ->where('is_entrance', false)
            ->count();

        $retailCount = $notification->actionGeofences()
            ->where('pointable_type', RetailOutlet::getMorphClass())
            ->count();

        if ($loadingCount == 0) {
            $point = $shipment->loadingZones()->first();
        } else {
            $point = $shipment->retailOutlets()->where('turn', $retailCount + 1)->first();
        }
        return $point;
    }

}
