<?php

namespace App\Jobs\CompleteShipment;

use App\Models\ShipmentList\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveWlnForShipment implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function __construct(Shipment $shipment, string $path)
    {
        $this->shipment = $shipment;
        $this->path = $path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $wObjects = \WialonResource::useOnlyHosts($this->shipment->w_conn_id)->getObjectsWithRegPlate();

        $wObject = \WialonResource::getObjectByRegPlate(
            $wObjects[$this->shipment->w_conn_id],
            [$this->shipment->car, $this->shipment->trailer]
        );

        $wln = \Wialon::useOnlyHosts([$this->shipment->w_conn_id])->returnRaw()
            ->exchange_export_messages(
                json_encode([
                    'format' => 'wln',
                    'itemId' => $wObject->id,
                    'compress' => 0,
                    'timeFrom' => strtotime($this->shipment->created_at),
                    'timeTo' => strtotime(now()),
                ])
            );

        \Storage::disk('completed-routes')->put($this->path.'messages.wln', $wln[$this->shipment->w_conn_id]);
    }

}
