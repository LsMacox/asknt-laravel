<?php

namespace App\Observers;

use App\Models\Wialon\WialonGeofence;
use App\Models\Wialon\WialonResources;

class WialonGeofenceObserver
{
    /**
     * Handle the WialonGeofence "created" event.
     *
     * @param  \App\Models\Wialon\WialonGeofence  $wialonGeofence
     * @return void
     */
    public function created(WialonGeofence $wialonGeofence)
    {
        //
    }

    /**
     * Handle the WialonGeofence "updated" event.
     *
     * @param  \App\Models\Wialon\WialonGeofence  $wialonGeofence
     * @return void
     */
    public function updated(WialonGeofence $wialonGeofence)
    {
        //
    }

    /**
     * Handle the WialonGeofence "deleted" event.
     *
     * @param  \App\Models\Wialon\WialonGeofence  $wialonGeofence
     * @return void
     */
    public function deleted(WialonGeofence $wialonGeofence)
    {
        $wResource = WialonResources::where('w_conn_id', $wialonGeofence->w_conn_id)->first();
        $params = [
            'itemId' => $wResource->w_id,
            'id' => $wialonGeofence->id,
            'callMode' => 'delete',
        ];

        \Wialon::useOnlyHosts([$wialonGeofence->w_conn_id])->resource_update_zone(
            json_encode($params)
        );
    }

    /**
     * Handle the WialonGeofence "restored" event.
     *
     * @param  \App\Models\Wialon\WialonGeofence  $wialonGeofence
     * @return void
     */
    public function restored(WialonGeofence $wialonGeofence)
    {
        //
    }

    /**
     * Handle the WialonGeofence "force deleted" event.
     *
     * @param  \App\Models\Wialon\WialonGeofence  $wialonGeofence
     * @return void
     */
    public function forceDeleted(WialonGeofence $wialonGeofence)
    {
        //
    }
}
