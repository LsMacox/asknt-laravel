<?php

namespace App\Http\Controllers\Api;

use App\Exports\CompletedRoutesExport;
use App\Filters\ShipmentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShipmentList\ShipmentFilterRequest;
use App\Models\ShipmentList\Shipment;
use App\Repositories\ShipmentRepository;


class ReportController extends Controller
{

    private $shipmentRepository;

    /**
     * DashboardController constructor.
     */
    public function __construct()
    {
        $this->shipmentRepository = app(ShipmentRepository::class);
    }

    /**
     * @param ShipmentFilterRequest $request
     * @param ShipmentFilter $filter
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(ShipmentFilterRequest $request, ShipmentFilter $filter) {
        $shipmentFilter = Shipment::filter($filter)->where('completed', true)->orWhere('not_completed', true);

        if ($shipmentFilter->count() > 0) {
            $shipmentStartDate = $shipmentFilter->first()->created_at;
            $shipmentEndDate = $shipmentFilter->get()->last()->created_at;
        }

        return response()->json(
            ['start_date' => $shipmentStartDate ?? null, 'end_date' => $shipmentEndDate ?? null],
            200
        );
    }

    /**
     * @param ShipmentFilterRequest $request
     * @param ShipmentFilter $filter
     * @return mixed
     */
    public function downloadReport (ShipmentFilterRequest $request, ShipmentFilter $filter) {
        $shipmentFilter = Shipment::filter($filter);

        $shipmentStartDate = $shipmentFilter->first()->created_at;
        $shipmentEndDate = $shipmentFilter->get()->last()->created_at;

        return \Excel::download(
            new CompletedRoutesExport($shipmentFilter),
            'asknt-report_'.$shipmentStartDate->format('d.m.Y').'-'.$shipmentEndDate->format('d.m.Y').'.xls'
        );
    }

}
