<?php

namespace App\Jobs\CompletedShipment;

use App\Models\ShipmentList\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveWlpForShipment implements ShouldQueue, ShouldBeUnique
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
    public function __construct(Shipment $shipment, $path)
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
        $objectParams = json_encode(array(
            'spec' => [
                'itemsType' => 'avl_unit',
                'propName' => '',
                'propValueMask' => '*',
                'sortType' => '',
                'propType' => '',
                'or_logic' => 0
            ],
            'force' => 0,
            'flags' => 4611686018427387903,
            'from' => 0,
            'to' => 0,
        ));

        $wObjects = \Wialon::useOnlyHosts([$this->shipment->w_conn_id])->returnRaw()->core_search_items($objectParams);

        $wlp = \Wialon::useOnlyHosts([$this->shipment->w_conn_id])->returnRaw()
            ->exchange_export_json(
                json_encode([
                    'fileName' => 'objects',
                    'json' => json_encode($wObjects[$this->shipment->w_conn_id])
                ])
            );

        \Storage::disk('completed-routes')->put($this->path.'objects.wlp', $wlp[$this->shipment->w_conn_id]);
    }

}
