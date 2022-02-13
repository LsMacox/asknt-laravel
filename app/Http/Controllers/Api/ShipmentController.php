<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShipmentMainResource;
use App\Repositories\ShipmentRepository;
use Illuminate\Http\Request;

class ShipmentController extends Controller
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request) {
        $items = $this->shipmentRepository->all();

        return response()->json(
            ShipmentMainResource::collection($items),
            200
        );
    }

}
