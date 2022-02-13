<?php


namespace App\Http\Controllers\Api;


use App\Models\Wialon\Action\ActionWialonGeofence;
use App\Models\Wialon\Action\ActionWialonTempViolation;
use Illuminate\Http\Request;
use App\Models\Wialon\WialonNotification;
use Illuminate\Support\Carbon;

class WialonActionsController
{

    /**
     * @param Request $request
     * @return void
     */
    public function tempViolation (Request $request) {
        \Log::channel('wialon-actions')->debug('tempViolation: '.json_encode($request->all()));

        $data = $this->normalizeRequest($request);

        $notification = WialonNotification::find($request->unit_id);
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
        ]);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function entranceToGeofence (Request $request) {
        \Log::channel('wialon-actions')->debug('entranceToGeofence: '.json_encode($request->all()));

        $data = $this->normalizeRequest($request);

        $notification = WialonNotification::find($request->unit_id);

        $notification->actionGeofences()->create([
            'name' => $data['zone'],
            'temp' => $data['sensor_temp'],
            'temp_type' => $data['sensor_temp_type'],
            'door_type' => $data['sensor_door_type'],
            'lat' => $data['lat'],
            'long' => $data['long'],
            'is_entrance' => true,
            'created_at' => $data['msg_time'],
        ]);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function departureFromGeofence (Request $request) {
        \Log::channel('wialon-actions')->debug('departureFromGeofence: '.json_encode($request->all()));

        $data = $this->normalizeRequest($request);

        $notification = WialonNotification::find($request->unit_id);

        $notification->actionGeofences()->create([
            'name' => $data['zone'],
            'temp' => $data['sensor_temp'],
            'temp_type' => $data['sensor_temp_type'],
            'door_type' => $data['sensor_door_type'],
            'lat' => $data['lat'],
            'long' => $data['long'],
            'is_entrance' => false,
            'created_at' => $data['msg_time'],
        ]);

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

    public function genReports (Request $request) {
        $notification = WialonNotification::find($request->unit_id);

        $shipment = $notification->shipment()->first();
        $resource = \WialonResource::useOnlyHosts($shipment->w_conn_id)
            ->firstResource()
            ->first();


        $reportTemplates = collect(optional(
            \WialonResource::useOnlyHosts($shipment->w_conn_id)
                ->getReportTemplates($shipment->w_conn_id, $resource->id))->rep
        );
        $reportTemplates->skip(0)->each(function ($template) use ($notification, $shipment, $resource) {
            $cleanup = \Wialon::useOnlyHosts([$shipment->w_conn_id])->report_cleanup_result();

            $intFrom = strtotime('01.02.2022 05:00');
            $intTo = strtotime('03.02.2022 23:00');

            $execParams = [
                'reportResourceId' => $resource->id,
                'reportTemplateId' => $template->id,
                'reportObjectId' => $notification->object_id,
                'reportObjectSecId' => 0,
                'remoteExec' => 0,
                'reportTemplate' => null,
                'interval' => [
                    'from' => 1644094800,//$intFrom,
                    'to' => 1644181199,//$intTo,
                    'flags' => 16777216,
                ],
            ];

            $execRes = \Wialon::useOnlyHosts([$shipment->w_conn_id])->report_exec_report(
                json_encode($execParams)
            );

            $rowsParams = [
                'tableIndex' => 0,
//                'indexFrom' => 0,
//                'indexTo' => 0,
                'config' => [
                    'type' => 'range',
                    'data' => [
                        'from' => 0,
                        'to' => 0,
                        'level' => 0,
                        'flat' => 0,
                        'rawValues' => 1,
                        'unitInfo' => 1,
                    ]
                ]
            ];

            $rowsRes = \Wialon::useOnlyHosts([$shipment->w_conn_id])->report_select_result_rows(
                json_encode($rowsParams)
            );

            dd($execRes, $rowsRes);

            $exportParams = [
                'format' => 4,
                'compress' => 1,
                'outputFileName' => '',
            ];

            $exportRes = \Wialon::useOnlyHosts([$shipment->w_conn_id])->report_export_result(
                json_encode($exportParams)
            );

            dd($exportRes);
        });
    }

}
