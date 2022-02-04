<?php


namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use App\Models\Wialon\WialonNotification;
use Wialon;
use WialonResource;

class WialonActionsController
{

    public function tempViolation (Request $request) {
        \Log::channel('wialon-actions')->debug('tempViolation: '.json_encode($request->all()));
    }

    public function entranceToGeofence (Request $request) {
        $notification = WialonNotification::find($request->unit_id);
        $shipment = $notification->shipment()->first();
        $resource = WialonResource::useOnlyHosts($shipment->w_conn_id)
                                ->firstResource()
                                ->first();
        $reportTemplates = WialonResource::useOnlyHosts($shipment->w_conn_id)->getReportTemplates($shipment->w_conn_id, $resource->id);

        \Wialon::useOnlyHosts([$shipment->w_conn_id])->report_cleanup_result();

        $execParams = [
            'reportResourceId' => $resource->id,
            'reportTemplateId' => 0,
            'reportObjectId' => '',
            'interval' => [
                'from' => 1623362456,
                'to' => 1643909456,
            ],
            'reportTemplate' => '',
        ];

        $exportRes = \Wialon::useOnlyHosts([$shipment->w_conn_id])->report_exec_report(
            json_encode($execParams)
        );

        $exportParams = [
            'format' => 4,
            'compress' => 1,
            'outputFileName' => '',
        ];

        $exportRes = \Wialon::useOnlyHosts([$shipment->w_conn_id])->report_export_result(
            json_encode($exportParams)
        );

        \Log::channel('wialon-actions')->debug('entranceToGeofence: '.json_encode($request->all()));
    }

    public function departureFromGeofence (Request $request) {
        \Log::channel('wialon-actions')->debug('departureFromGeofence: '.json_encode($request->all()));
    }

}
