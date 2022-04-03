<?php

namespace App\Http\Resources;

use App\Facades\ShipmentDataService;
use App\Models\Wialon\WialonNotification;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardMainResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $actGeofences = ShipmentDataService::wialonNotificationAction(
            $this->wialonNotifications,
            WialonNotification::ACTION_GEOFENCE,
            true
        );
        $actTemps = ShipmentDataService::wialonNotificationAction(
            $this->wialonNotifications,
            WialonNotification::ACTION_TEMP,
            true
        );

        $curr_temp = null;
        $is_temp_violation = null;

        if ($actTemps) {
            $curr_temp = optional($actTemps->last())->temp;
            $tempFrom = $this->temperature['from'];
            $tempTo = $this->temperature['to'];
            $is_temp_violation = !($curr_temp < $tempTo && $curr_temp > $tempFrom);
        }

        return [
            'id' => $this->id,
            'date_shipping' => $this->date,
            'car' => $this->car ?? $this->trailer,
            'driver' => $this->driver,
            'loading_warehouse' => optional($this->loadingZones->first())->name,
            'violations' => ViolationResource::collection($this->violations),
            'weight' => $this->weight,
            'curr_temp' => !!$curr_temp ? round($curr_temp, 1) : '?',
            'is_temp_violation' => $is_temp_violation,
            'points_total' => $this->shipmentRetailOutlets->count() + 1,
            'points_completed' => $actGeofences ? $actGeofences->where('is_entrance', true)->count() : 0,
        ];
    }
}
