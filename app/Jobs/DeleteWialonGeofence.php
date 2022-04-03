<?php

namespace App\Jobs;

use App\Models\Wialon\WialonGeofence;
use App\Models\Wialon\WialonResources;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteWialonGeofence implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected WialonGeofence $wialonGeofence;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WialonGeofence $wialonGeofence) {
        $this->wialonGeofence = $wialonGeofence;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $wResource = WialonResources::where('w_conn_id', $this->wialonGeofence->w_conn_id)->first();

        $params = [
            'itemId' => $wResource->w_id,
            'id' => $this->wialonGeofence->id,
            'callMode' => 'delete',
        ];

        \Wialon::useOnlyHosts([$this->wialonGeofence->w_conn_id])->resource_update_zone(
            json_encode($params)
        );
    }
}
