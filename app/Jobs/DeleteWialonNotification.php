<?php

namespace App\Jobs;

use App\Models\Wialon\WialonNotification;
use App\Models\Wialon\WialonResources;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteWialonNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 10;

    protected WialonNotification $wialonNotification;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WialonNotification $wialonNotification)
    {
        $this->onQueue('wialon');
        $this->wialonNotification = $wialonNotification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $wResource = WialonResources::where('w_conn_id', $this->wialonNotification->w_conn_id)->first();

        $params = [
            'itemId' => $wResource->w_id,
            'id' => $this->wialonNotification->id,
            'callMode' => 'delete',
        ];

        \Wialon::newSession($this->wialonNotification->w_conn_id);

        $wUpdate = \Wialon::useOnlyHosts([$this->wialonNotification->w_conn_id])->resource_update_notification(
            json_encode($params)
        );

        if (!isset($wUpdate[$this->wialonNotification->w_conn_id][0])) {
            $this->release(10);
        } else {
            $this->wialonNotification->delete();
        }
    }
}
