<?php


namespace App\Http\Controllers\Api;


use App\Models\LoadingZone;
use App\Models\RetailOutlet;
use App\Models\Wialon\Action\ActionWialonGeofence;
use App\Models\Wialon\Action\ActionWialonTempViolation;
use Illuminate\Http\Request;
use App\Models\Wialon\WialonNotification;
use Illuminate\Support\Carbon;

class WialonActionsController
{

    protected $shipment;

    /**
     * @param Request $request
     * @return void
     */
    public function tempViolation (Request $request) {
        \Log::channel('wialon-actions')->debug('tempViolation: '.json_encode($request->all()));

        $data = $this->normalizeRequest($request);

        $notification = WialonNotification::where('name', $request->notification)->first();
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
    public function entranceToGeofence (Request $request) {
        \Log::channel('wialon-actions')->debug('entranceToGeofence: '.json_encode($request->all()));

        $data = $this->normalizeRequest($request);

        $notification = WialonNotification::where('name', $request->notification)->first();
        $shipment = $notification->shipment()->first();

        $this->shipment = $shipment;

        $loadingCount = $notification->actionGeofences()
            ->where('pointable_type', LoadingZone::getMorphClass())
            ->count();

        $retailCount = $notification->actionGeofences()
            ->where('is_entrance', true)
            ->where('pointable_type', RetailOutlet::getMorphClass())
            ->count();

        $wialonTotal = [];

        if ($retailCount > 0) {
            $loadingType = $notification->actionGeofences()
                                        ->where('pointable_type', LoadingZone::getMorphClass())->first();

            $retailType = $notification->actionGeofences()
                ->where('pointable_type', RetailOutlet::getMorphClass())
                ->orderBy('created_at','desc')->first();

            $wialonTotal = $this->getWialonTotal($request->notification, $loadingType->created_at, $retailType->created_at);
        }

        if ($loadingCount == 0) {
            $point = $shipment->loadingZone()->first();
        } else {
            $point = $shipment->retailOutlets()->where('turn', $retailCount + 1)->first();
        }

        $actGeofence = $point->actionWialonGeofences()->create(array_merge([
            'wialon_notification_id' => $notification->id,
            'name' => $data['zone'],
            'temp' => $data['sensor_temp'],
            'temp_type' => $data['sensor_temp_type'],
            'door' => $data['sensor_door_type'],
            'lat' => $data['lat'],
            'long' => $data['long'],
            'is_entrance' => true,
            'created_at' => $data['msg_time'],
        ], $wialonTotal));


        if ($point instanceof RetailOutlet) {
            $shipmentRetailOutlet = $point->shipmentRetailOutlet()->first();
            $arrive_from_plan = Carbon::parse($shipmentRetailOutlet->arrive_from)->format('H:i');
            $arrive_to_plan = Carbon::parse($shipmentRetailOutlet->arrive_from)->format('H:i');

            if ($arrive_from_plan && $arrive_to_plan) {
                $planStart = Carbon::parse($arrive_from_plan);
                $planFinish = Carbon::parse($arrive_to_plan);
                $actualFinish = Carbon::parse($actGeofence->created_at);

                $late = !$actualFinish->between($planStart, $planFinish);

                if ($late) {
                    $shipment->violations()->create([
                        'name' => 'Опоздание на ТТ',
                        'text' => 'Прибытие в '. $actualFinish->format('H:i').', норма  '.$arrive_from_plan.'-'.$arrive_to_plan,
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

        $notification = WialonNotification::where('name', $request->notification)->first();
        $shipment = $notification->shipment()->first();
        $this->shipment = $shipment;
//        $this->genReports($request);

        $loadingCount = $notification->actionGeofences()
            ->where('pointable_type', LoadingZone::getMorphClass())
            ->where('is_entrance', false)
            ->count();

        $retailCount = $notification->actionGeofences()
            ->where('is_entrance', true)
            ->where('pointable_type', RetailOutlet::getMorphClass())
            ->count();

        $wialonTotal = [];

        if ($retailCount > 0) {
            $loadingType = $notification->actionGeofences()
                ->where('pointable_type', LoadingZone::getMorphClass())->first();

            $retailType = $notification->actionGeofences()
                ->where('pointable_type', RetailOutlet::getMorphClass())
                ->orderBy('created_at','desc')->first();

            $wialonTotal = $this->getWialonTotal($request->notification, $loadingType->created_at, $retailType->created_at);
        }

        if ($loadingCount == 0) {
            $point = $shipment->loadingZone()->first();
        } else {
            $point = $shipment->retailOutlets()->where('turn', $retailCount == 0 ? 1 : $retailCount)->first();
        }

        $point->actionWialonGeofences()->create(array_merge([
            'wialon_notification_id' => $notification->id,
            'name' => $data['zone'],
            'temp' => $data['sensor_temp'],
            'temp_type' => $data['sensor_temp_type'],
            'door' => $data['sensor_door_type'],
            'lat' => $data['lat'],
            'long' => $data['long'],
            'is_entrance' => false,
            'created_at' => $data['msg_time'],
        ], $wialonTotal));
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

    public function getWialonTotal ($notificationName, Carbon $from, Carbon $to) {
        $notification = WialonNotification::where('name', $notificationName)->first();

        $resource = \WialonResource::useOnlyHosts($this->shipment->w_conn_id)
            ->firstResource()
            ->first();

        $reportTemplates = collect(optional(
            \WialonResource::useOnlyHosts($this->shipment->w_conn_id)
                ->getReportTemplates($this->shipment->w_conn_id, $resource->id))->rep
        );

        \Wialon::useOnlyHosts([$this->shipment->w_conn_id])->report_cleanup_result();

        $execParams = [
            'reportResourceId' => $resource->id,
            'reportTemplateId' => $reportTemplates[3]->id,
            'reportObjectId' => $notification->object_id,
            'reportObjectSecId' => 0,
            'remoteExec' => 0,
            'reportTemplate' => null,
            'interval' => [
                'from' => strtotime($from->toIso8601String()),
                'to' => strtotime($to->toIso8601String()),
                'flags' => 16777216,
            ],
        ];

        $execRes = \Wialon::useOnlyHosts([$this->shipment->w_conn_id])->report_exec_report(
            json_encode($execParams)
        );

        $duration = optional(optional($execRes->first()->first()->tables[0])->total)[8];
        $mileage = optional(optional($execRes->first()->first()->tables[0])->total)[9];

        return compact('duration', 'mileage');
    }

    public function genReports (Request $request) {
        $notification = WialonNotification::where('name', $request->notification)->first();
        $actionGeofences = $notification->actionGeofences()->orderBy('created_at')->get();

        $retailOutlets = $this->shipment->retailOutlets()->orderBy('turn')->get();

        $isDepartureLastGeofence = $actionGeofences->where('pointable_type', RetailOutlet::getMorphClass())
                ->where('pointable_id', $retailOutlets->last()->id)->where('is_entrance', false)->isNotEmpty();

//        if ($isDepartureLastGeofence) {

            $shipment = $notification->shipment()->first();
            $resource = \WialonResource::useOnlyHosts($this->shipment->w_conn_id)
                ->firstResource()
                ->first();

            $firstEntrance = $actionGeofences->first();
            $lastDeparture = $actionGeofences->last();

            $reports = [];

            $reportTemplates = collect(optional(
                \WialonResource::useOnlyHosts($this->shipment->w_conn_id)
                    ->getReportTemplates($this->shipment->w_conn_id, $resource->id))->rep
            );

            $reportTemplates->each(function ($template) use ($notification, &$reports, $shipment, $resource, $firstEntrance, $lastDeparture) {
                $cleanup = \Wialon::useOnlyHosts([$shipment->w_conn_id])->report_cleanup_result();

                $intFrom = strtotime($firstEntrance->created_at);
                $intTo = strtotime($lastDeparture->created_at);

                $execParams = [
                    'reportResourceId' => $resource->id,
                    'reportTemplateId' => $template->id,
                    'reportObjectId' => $notification->object_id,
                    'reportObjectSecId' => 0,
                    'remoteExec' => 0,
                    'reportTemplate' => null,
                    'interval' => [
                        'from' => $intFrom,
                        'to' => $intTo,
                        'flags' => 16777216,
                    ],
                ];

                $execRes = \Wialon::useOnlyHosts([$shipment->w_conn_id])->report_exec_report(
                    json_encode($execParams)
                );

//                dd($execRes);

                if ($execRes->first()) {
                    $duration = optional(optional($execRes->first()->first()->tables[0])->total)[8];
                    $mileage = optional(optional($execRes->first()->first()->tables[0])->total)[9];
                }

                $exportParams = [
                    'format' => 4,
                    'compress' => 1,
                    'outputFileName' => 'hazanYbiyza',
                ];

                $exportRes = \Wialon::useOnlyHosts([$shipment->w_conn_id])->returnRaw()->report_export_result(
                    json_encode($exportParams)
                );

                $reports[] = $exportRes[$shipment->w_conn_id];
            });

            dd($reports);
//        }
    }

}
