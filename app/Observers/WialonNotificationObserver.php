<?php

namespace App\Observers;

use App\Models\Wialon\WialonNotification;
use App\Models\Wialon\WialonResources;

class WialonNotificationObserver
{
    /**
     * Handle the WialonNotification "created" event.
     *
     * @param  \App\Models\Wialon\WialonNotification  $wialonNotification
     * @return void
     */
    public function created(WialonNotification $wialonNotification)
    {
        //
    }

    /**
     * Handle the WialonNotification "updated" event.
     *
     * @param  \App\Models\Wialon\WialonNotification  $wialonNotification
     * @return void
     */
    public function updated(WialonNotification $wialonNotification)
    {
        //
    }

    /**
     * Handle the WialonNotification "deleted" event.
     *
     * @param  \App\Models\Wialon\WialonNotification  $wialonNotification
     * @return void
     */
    public function deleted(WialonNotification $wialonNotification)
    {
        $wResource = WialonResources::where('w_conn_id', $wialonNotification->w_conn_id)->first();
        $params = [
            'itemId' => $wResource->w_id,
            'id' => $wialonNotification->id,
            'callMode' => 'delete',
        ];

        \Wialon::useOnlyHosts([$wialonNotification->w_conn_id])->resource_update_notification(
            json_encode($params)
        );
    }

    /**
     * Handle the WialonNotification "restored" event.
     *
     * @param  \App\Models\Wialon\WialonNotification  $wialonNotification
     * @return void
     */
    public function restored(WialonNotification $wialonNotification)
    {
        //
    }

    /**
     * Handle the WialonNotification "force deleted" event.
     *
     * @param  \App\Models\Wialon\WialonNotification  $wialonNotification
     * @return void
     */
    public function forceDeleted(WialonNotification $wialonNotification)
    {
        //
    }
}
