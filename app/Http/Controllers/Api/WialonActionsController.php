<?php


namespace App\Http\Controllers\Api;

use App\Models\LoadingZone;
use App\Models\ShipmentList\Shipment;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use App\Models\Wialon\Action\ActionWialonGeofence;
use App\Models\Wialon\Action\ActionWialonTempViolation;
use Illuminate\Http\Request;
use App\Models\Wialon\WialonNotification;
use Illuminate\Support\Carbon;
use App\Facades\ShipmentDataService;

class WialonActionsController
{

    /**
     * @param Request $request
     * @return void
     */
    public function tempViolation(Request $request) {
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
    public function temp(Request $request) {
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
    public function entranceToGeofence(Request $request) {
        \Log::channel('wialon-actions')->debug('entranceToGeofence: '.json_encode($request->all()));

        $data = $this->normalizeRequest($request);

        $notification = WialonNotification::where('name', $request->notification)->firstOrFail();
        $shipment = $notification->shipment()->first();

        $point = $this->getPoint($notification, $shipment, true);

        $actGeofence = $point->actionWialonGeofences()->create([
            'wialon_notification_id' => $notification->id,
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

        // Create a violation if there is a delay from the plan
        if ($point instanceof ShipmentRetailOutlet) {
            $actualFinish = Carbon::parse($actGeofence->created_at);

            $planStart = $point->planStart;
            $planFinish = $point->planFinish;

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

    /**
     * @param Request $request
     * @return void
     */
    public function departureFromGeofence(Request $request) {
        \Log::channel('wialon-actions')->debug('departureFromGeofence: '.json_encode($request->all()));

        $data = $this->normalizeRequest($request);

        $wNotification = WialonNotification::where('name', $request->notification)->firstOrFail();
        $shipment = $wNotification->shipment()->first();

        $point = $this->getPoint($wNotification, $shipment, false);

        $point->actionWialonGeofences()->create([
            'wialon_notification_id' => $wNotification->id,
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
        $retailOutlets = $shipment->shipmentRetailOutlets()
            ->get()
            ->sortBy('turn');

        if (
            $retailOutlets->count() > 0 &&
            $point->id === $retailOutlets->last()->id
        ) {
            ShipmentDataService::completeShipment($shipment);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function normalizeRequest(Request $request) {
        $data = $request->all();

        if ($request->has('sensor_temp')) {
            $data['sensor_temp'] = \Str::of($data['sensor_temp'])
                ->remove('Средняя температура iQF:');

            $data['sensor_temp_type'] = ActionWialonTempViolation::getTempType($data['sensor_temp']);

            $data['sensor_temp'] = (double) (string) $data['sensor_temp']
                ->replaceMatches('/('.implode('|',ActionWialonTempViolation::ENUM_TEMP).')/', '')
                ->trim();
        }

        if ($request->has('msg_time')) {
            $data['msg_time'] = Carbon::parse($data['msg_time'])->toIso8601String();
        }

        if ($request->has('mileage')) {
            $data['mileage'] = (integer) (string) \Str::of($data['mileage'])
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
    public function getPoint(WialonNotification $notification, Shipment $shipment, $entrance = true)
    {
        $actGeofences = ShipmentDataService::wialonNotificationAction(
            $notification,
            WialonNotification::ACTION_GEOFENCE,
        );

        $loadingZoneGeofences = $actGeofences
            ->where('pointable_type', LoadingZone::getMorphClass());

        if ($loadingZoneGeofences->count() == 0 ||
            ($loadingZoneGeofences->where('is_entrance', true)->first() && $loadingZoneGeofences->count() == 1 && !$entrance)) {
            $point = $shipment->loadingZones()->first();
        } else if ($loadingZoneGeofences->where('is_entrance', true)->first() && $loadingZoneGeofences->count() == 1 && $entrance) {
            throw new \Exception('There must be an exit from the loading zone');
        } else {
            $lastGeofence = $actGeofences
                ->where('pointable_type', ShipmentRetailOutlet::getMorphClass())
                ->where('pointable_id', $actGeofences->last()->pointable_id);

            if ((!$lastGeofence->first() || $lastGeofence->count() == 2) && $entrance) {
                $count = $actGeofences
                    ->where('pointable_type', ShipmentRetailOutlet::getMorphClass())
                    ->where('is_entrance', true)
                    ->count();

                $point = $shipment->shipmentRetailOutlets()->where('turn', $count + 1)->first();
            } else {
                if ($entrance && $lastGeofence->where('is_entrance', true)->first()) {
                    throw new \Exception('You cannot enter the same geofence twice');
                } else if (!$entrance && ($lastGeofence->where('is_entrance', false)->first() || !$lastGeofence->first())) {
                    throw new \Exception('You cannot leave without entering the geofence');
                } else {
                    $point = $lastGeofence->first()->pointable()->first();
                }
            }
        }

        return $point;
    }

}
