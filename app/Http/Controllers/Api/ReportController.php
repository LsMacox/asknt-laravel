<?php

namespace App\Http\Controllers\Api;


use App\Exports\CompletedRoutesExport;
use App\Filters\ShipmentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShipmentList\ShipmentFilterRequest;
use App\Models\ShipmentList\Shipment;

class ReportController extends Controller
{
    /**
     * @param ShipmentFilterRequest $request
     * @param ShipmentFilter $filter
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(ShipmentFilterRequest $request, ShipmentFilter $filter) {
        $shipmentFilter = Shipment::filter($filter)
            ->where('completed', true)
            ->orWhere('not_completed', true)
            ->orderBy('created_at')->get();

        $data = ['start_date' => now(), 'end_date' => now()];
        $shipmentStartDate = null;
        $shipmentEndDate = null;

        if ($shipmentFilter->count() > 0) {
            $shipmentStartDate = $shipmentFilter->first()->created_at;
            $shipmentEndDate = $shipmentFilter->last()->created_at;
        }

        if ($shipmentStartDate) {
            $data = ['start_date' => $shipmentStartDate, 'end_date' => $shipmentEndDate ?? null];
        }

        return response()->json($data, 200);
    }

    /**
     * @param ShipmentFilterRequest $request
     * @param ShipmentFilter $filter
     * @return mixed
     */
    public function downloadReport (ShipmentFilterRequest $request, ShipmentFilter $filter) {
        $shipmentFilter = Shipment::filter($filter)
            ->where('completed', true)
            ->orWhere('not_completed', true)
            ->orderBy('created_at');

        $shipmentStartDate = optional($shipmentFilter->get()->first())->created_at ?? now();
        $shipmentEndDate = optional($shipmentFilter->get()->last())->created_at ?? now();

        return \Excel::download(
            new CompletedRoutesExport($shipmentFilter),
            'asknt-report_'.$shipmentStartDate->format('d.m.Y').'-'.$shipmentEndDate->format('d.m.Y').'.xls'
        );
    }

}
