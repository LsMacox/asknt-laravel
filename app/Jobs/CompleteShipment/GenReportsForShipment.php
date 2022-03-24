<?php

namespace App\Jobs\CompleteShipment;

use App\Models\ShipmentList\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenReportsForShipment implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var object $actionGeofences
     */
    protected $actionGeofences;

    /**
     * @var object $notification
     */
    protected $notification;

    /**
     * @var object $resource
     */
    protected $resource;

    /**
     * @var Shipment $shipment
     */
    protected $shipment;

    /**
     * @var string $path
     */
    public $path;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Shipment $shipment, string $path, $notification, $resource, $actionGeofences)
    {
        $this->shipment = $shipment;
        $this->notification = $notification;
        $this->resource = $resource;
        $this->actionGeofences = $actionGeofences;
        $this->path = $path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $reportTemplates = collect(optional(
            \WialonResource::useOnlyHosts($this->shipment->w_conn_id)
                ->getReportTemplates($this->shipment->w_conn_id, $this->resource->id))->rep
        );

        $firstEntrance = $this->actionGeofences->first();
        $lastDeparture = $this->actionGeofences->last();

        $reportTemplates->each(function ($template) use ($firstEntrance, $lastDeparture) {
            $cleanup = \Wialon::useOnlyHosts([$this->shipment->w_conn_id])->report_cleanup_result();

            $intFrom = strtotime($firstEntrance->created_at);
            $intTo = strtotime($lastDeparture->created_at);

            $execParams = [
                'reportResourceId' => $this->resource->id,
                'reportTemplateId' => $template->id,
                'reportObjectId' => $this->notification->object_id,
                'reportObjectSecId' => 0,
                'remoteExec' => 0,
                'reportTemplate' => null,
                'interval' => [
                    'from' => $intFrom,
                    'to' => $intTo,
                    'flags' => 16777216,
                ],
            ];

            $execRes = \Wialon::useOnlyHosts([$this->shipment->w_conn_id])->report_exec_report(
                json_encode($execParams)
            );

            $exportParams = [
                'format' => 8,
                'pageWidth' => 0,
                'headings' => 1,
                'compress' => 0,
                'attachMap' => 0,
                'hideMapBasis' => 0
            ];

            $exportRes = \Wialon::useOnlyHosts([$this->shipment->w_conn_id])->returnRaw()->report_export_result(
                json_encode($exportParams)
            );

            $fileName = $template->n.'.xlsx';

            \Storage::disk('completed-routes')->put($this->path.$fileName, $exportRes[$this->shipment->w_conn_id]);
        });
    }

}
