<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\SoapServices\Struct\ShipmentStatus\DT_Shipment_ERP_resp;
use Illuminate\Support\Facades\Storage;

class SendShipmentStatus implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\SoapServices\Struct\ShipmentStatus\DT_Shipment_ERP_resp
     */
    protected $dt_shipment_erp_resp;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DT_Shipment_ERP_resp $dt_shipment_erp_resp)
    {
        $this->dt_shipment_erp_resp = $dt_shipment_erp_resp;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $login = config('soap-server.wsdl.shipment-status.username');
        $password = config('soap-server.wsdl.shipment-status.password');

//        $wsdl = route('avantern.shipment_status.wsdl');
        $wsdl = Storage::disk('wsdl')->path('avantern/Avantern_ShipmentStatus_Service.wsdl');

        $client = new \Laminas\Soap\Client($wsdl, ['login' => $login, 'password' => $password]);

        if (!config('app.debug')) {
            $client->SI_ShipmentStatus_Avantern_Async_Out($this->dt_shipment_erp_resp);
        } else {
            \Log::channel('my-jobs')
                ->debug('SendShipmentStatus['.$this->dt_shipment_erp_resp->waybill->number.']: '.json_encode($this->dt_shipment_erp_resp));
        }
    }

}
