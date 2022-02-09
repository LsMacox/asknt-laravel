<?php

namespace App\Http\Controllers\Api;

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
        $shipments = Shipment::filter($filter)->get();
//        dd($shipments);
//
//        return response()->json(
//            compact( 'total', 'items'),
//            200
//        );
    }

}
