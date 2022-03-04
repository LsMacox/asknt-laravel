<?php

namespace App\Http\Controllers\Api;

use App\Filters\ShipmentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShipmentList\ShipmentFilterRequest;
use App\Http\Resources\CompletedRoutesResource;
use App\Models\ShipmentList\Shipment;
use App\Repositories\ShipmentRepository;
use Illuminate\Http\Request;

class CompletedRoutesController extends Controller
{

    private $shipmentRepository;

    /**
     * CompletedRoutesController constructor.
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
                ->where('completed', true)
                ->orWhere('not_completed', true)
        );
        $items = CompletedRoutesResource::collection($items);

        return response()->json(
            compact( 'total', 'items'),
            200
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadShipmentFiles (Request $request) {
        $shipment = Shipment::where('id', $request->shipment_id)
            ->where('completed', true)
            ->orWhere('not_completed', true)
            ->first();

        if (!$shipment) {
            abort(404);
        }

        $zipFile = 'asknt-files.zip';

        $zip = new \ZipArchive();
        $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = \Storage::disk('completed-routes')->files($shipment->date->format('d.m.Y').'/'.$shipment->id);
        foreach ($files as $file) {
            $path = \Storage::disk('completed-routes')->path($file);
            $zip->addFile($path, $file);
        }
        $zip->close();

        return response()->download($zipFile);
    }

}
