<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardDetailResource;
use App\Http\Resources\ViolationResource;
use App\Models\Violation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class ViolationController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listViolationByShipment(Request $request) {
        $violations = Violation::where('shipment_id', $request->shipment_id)
            ->where('repaid', false)
            ->get();

        return response()->json(
            ViolationResource::collection($violations),
            200
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function repaidViolation(Request $request) {
        $violations = Violation::whereIn('id', $request->ids)->get();

        $violations->each(function ($v) use ($request) {
            $v->repaid = true;
            $v->repaid_description = $request->repaid_description;
            $v->save();
        });

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function readViolation(Request $request) {
        $violations = Violation::whereIn('id', $request->ids)->get();

        $violations->each(function ($v) use ($request) {
            $v->read = true;
            $v->save();
        });

        return response('', Response::HTTP_NO_CONTENT);
    }

}
