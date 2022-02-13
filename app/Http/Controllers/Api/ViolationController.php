<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardDetailResource;
use App\Models\Violation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class ViolationController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listViolationByShipment (Request $request) {
        $violations = Violation::where('shipment_id', $request->shipment_id)
            ->where('repaid', false)
            ->get();

        return response()->json(
            $violations,
            200
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function repaidViolation (Request $request) {
        $violation = Violation::findOrFail($request->id);
        $violation->repaid = true;
        $violation->repaid_description = $request->repaid_description;
        $violation->save();

        return response('', Response::HTTP_NO_CONTENT);
    }

}
