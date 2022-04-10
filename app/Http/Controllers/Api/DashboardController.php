<?php

namespace App\Http\Controllers\Api;

use App\Filters\ShipmentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShipmentList\ShipmentFilterRequest;
use App\Http\Resources\DashboardDetailResource;
use App\Http\Resources\DashboardMainResource;
use App\Models\ShipmentList\Shipment;
use App\Repositories\ShipmentRepository;
use Illuminate\Http\Request;


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
            $inp('sortByDesc') ?? true,
            Shipment::filter($filter)
                ->where('completed', false)
                ->where('not_completed', false)
            ->with([
                'shipmentRetailOutlets',
                'loadingZones',
                'violations' => function ($query) {
                    $query
                        ->where('read', false)
                        ->where('repaid', false);
                },
                'wialonNotifications.actionGeofences',
                'wialonNotifications.actionTemps'
            ])
        );
        $items = DashboardMainResource::collection($items);

        return response()->json(
            compact( 'total', 'items'),
            200
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetailByShipmentId (Request $request) {
        $shipment = Shipment::where('id', $request->shipment_id)
            ->where('completed', false)
            ->where('not_completed', false)
            ->with([
                'loadingZones.shipments',
                'loadingZones.actionWialonGeofences',
                'shipmentRetailOutlets.shipmentOrders' => function ($query) use ($request) {
                    $query->where('shipment_id', $request->shipment_id);
                },
                'shipmentRetailOutlets.actionWialonGeofences',
                'shipmentRetailOutlets.retailOutlet.actionWialonGeofences',
                'shipmentRetailOutlets.shipments',
                'wialonNotifications.actionGeofences',
                'wialonNotifications.actionTemps'
            ])
            ->first();

        if (!$shipment) {
            abort(404);
        }

        return response()->json(
            new DashboardDetailResource($shipment),
            200
        );
    }

}
