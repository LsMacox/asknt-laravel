<?php

namespace App\Jobs;

use App\Jobs\CompleteShipment\GenReportsForShipment;
use App\Jobs\CompleteShipment\SaveKmlForShipment;
use App\Jobs\CompleteShipment\SaveWlnForShipment;
use App\Jobs\CompleteShipment\SaveWlpForShipment;
use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\WialonNotification;
use App\Models\Wialon\WialonResources;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompletionShipments implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $shipments = Shipment::where('completed', false)->where('not_completed', false)->all();

        $shipments->each(function ($shipment) {
            if (now()->diffInDays($shipment->lastArriveDate) > 1) {
                $wNotification = $shipment->wialonNotifications()->where('action_type', WialonNotification::ACTION_GEOFENCE)->first();
                $actionGeofences = $wNotification->actionGeofences()->orderBy('created_at')->get();

                $wResource = WialonResources::where('w_conn_id', $shipment->w_conn_id)->first();

                $path = $shipment->date->format('d.m.Y').'/'.$shipment->id.'/';

                $this->batch()->add([
                    new SaveWlnForShipment($shipment, $path),
                    new SaveKmlForShipment($shipment, $path, $wResource),
                    new GenReportsForShipment($shipment, $path, $wNotification, $wResource, $actionGeofences),
//                    new SaveWlpForShipment($shipment, $path)
                ]);

                $shipment->update(['not_completed' => true]);
            }
        });
    }
}
