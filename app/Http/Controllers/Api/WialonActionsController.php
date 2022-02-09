<?php


namespace App\Http\Controllers\Api;


use App\Exports\CompletedRoutesExport;
use App\Http\Requests\Api\WialonActions\GeofenceRequest;
use Illuminate\Http\Request;
use App\Models\Wialon\WialonNotification;
use Wialon;
use WialonResource;

class WialonActionsController
{

    public function tempViolation (Request $request) {
        \Log::channel('wialon-actions')->debug('tempViolation: '.json_encode($request->all()));
    }

    public function entranceToGeofence (GeofenceRequest $request) {
        return \Excel::download(new CompletedRoutesExport, 'completed-routes.xls');

        $notification = WialonNotification::find($request->unit_id);

        $shipment = $notification->shipment()->first();
        $resource = WialonResource::useOnlyHosts($shipment->w_conn_id)
                                ->firstResource()
                                ->first();
        $reportTemplates = collect(optional(
            WialonResource::useOnlyHosts($shipment->w_conn_id)
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


        \Log::channel('wialon-actions')->debug('entranceToGeofence: '.json_encode($request->all()));
    }

    public function departureFromGeofence (Request $request) {
        \Log::channel('wialon-actions')->debug('departureFromGeofence: '.json_encode($request->all()));
    }

}
