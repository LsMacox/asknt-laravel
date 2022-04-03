<?php

namespace App\Services;

use App\Jobs\CompletedShipment\GenReportsForShipment;
use App\Jobs\CompletedShipment\SaveKmlForShipment;
use App\Jobs\CompletedShipment\SaveWlnForShipment;
use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\WialonNotification;
use App\Models\Wialon\WialonResources;
use Symfony\Component\String\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\Bus;

class ShipmentDataService
{

    /**
     * @param Shipment $shipment
     * @return void
     */
    public function completeShipment (Shipment $shipment, $completed = true) {
        $wNtfGeofence = $shipment->wialonNotifications()
            ->where('action_type', WialonNotification::ACTION_GEOFENCE)
            ->first();

        $actGeofences = $this->wialonNotificationAction(
            $shipment->wialonNotifications(),
            WialonNotification::ACTION_GEOFENCE
        );

        $wResource = WialonResources::where('w_conn_id', $shipment->w_conn_id)->first();

        $path = $shipment->date->format('d.m.Y').'/'.$shipment->id.'/';

        Bus::chain([
            new SaveWlnForShipment($shipment, $path),
            new SaveKmlForShipment($shipment, $path, $wResource),
            new GenReportsForShipment($shipment, $path, $wNtfGeofence, $wResource, $actGeofences),
//                    new SaveWlpForShipment($shipment, $path)
        ]);

        $shipment->update($completed ? ['completed' => true] : ['not_completed' => true]);
    }

    /**
     * Getting a relations in the action table
     * @param $wialonNotifications
     * @param $action
     * @param bool $lazy
     * @return mixed|null
     */
    public function wialonNotificationAction($wialonNotifications, $action, bool $lazy = false) {
        $notifications = $wialonNotifications
                    ->where('action_type', $action);

        if ($lazy) {
            if ($notifications->isEmpty()) return null;
        } else {
            if ($notifications->count() === 0) return null;
            $notifications = $notifications->get();
        }

        $actRelation = null;

        switch ($action) {
            case WialonNotification::ACTION_TEMP:
                $actRelation = 'actionTemps';
                break;
            case WialonNotification::ACTION_GEOFENCE:
                $actRelation = 'actionGeofences';
                break;
            case WialonNotification::ACTION_TEMP_VIOLATION:
                $actRelation = 'actionTempViolations';
                break;
        }

        if (!$actRelation) {
            throw new InvalidArgumentException('Not a valid argument action');
        }

        $acts = null;

        if ($lazy) {
            foreach ($notifications as $notification) {
                $query = $notification->$actRelation->sortBy('created_at');
                $acts = !$acts ? $query : $query->merge($acts);
            }
        } else {
            foreach ($notifications as $notification) {
                $query = $notification->$actRelation()->latest()->get();
                $acts = !$acts ? $query : $query->merge($acts);
            }
        }

        return $acts;
    }

}
