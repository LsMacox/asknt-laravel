<?php

namespace App\Http\Controllers\Api;

use App\Filters\ShipmentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShipmentList\ShipmentFilterRequest;
use App\Http\Resources\DashboardMainResource;
use App\Models\ShipmentList\Shipment;
use App\Repositories\ShipmentRepository;


class DashboardController extends Controller
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
        $inp = function ($val) use ($request) {
            return $request->input($val);
        };

        [$total, $items] = $this->shipmentRepository->clientPaginate(
            $inp('offset'),
            $inp('limit'),
            $inp('sortBy'),
            $inp('sortByDesc') || false,
            Shipment::filter($filter)
        );
        $items = DashboardMainResource::collection($items);

        return response()->json(
            compact( 'total', 'items'),
            200
        );
    }

}
