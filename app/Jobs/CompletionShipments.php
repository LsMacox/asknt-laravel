<?php

namespace App\Jobs;

use App\Facades\ShipmentDataService;
use App\Models\ShipmentList\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompletionShipments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $shipments = Shipment::where('completed', false)->where('not_completed', false)->all();

        $shipments->each(function ($shipment) {
            if (now()->diffInDays($shipment->lastArriveDate) > 1) {
                ShipmentDataService::completeShipment($shipment, false);
            }
        });
    }
}
