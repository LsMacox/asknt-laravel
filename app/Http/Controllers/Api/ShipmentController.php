<?php


namespace App\Http\Controllers\Api;


use App\Http\Resources\ShipmentResource;
use App\Models\ShipmentList\Shipment;


class ShipmentController
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function list () {
        return response()->json(
            ShipmentResource::collection(Shipment::all())
        );
    }

}
