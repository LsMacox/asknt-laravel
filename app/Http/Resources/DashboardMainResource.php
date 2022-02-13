<?php

namespace App\Http\Resources;

use App\Models\Wialon\WialonNotification;
use App\Repositories\LoadingZoneRepository;
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
        $actionGeofences = $this->wialonNotifications->where('action_type', WialonNotification::ACTION_GEOFENCE)
            ->map(function ($n) {
                return $n->actionGeofences;
            })
            ->flatten(1)
            ->sortBy('created_at');


        return [
            'id' => $this->id,
            'date_shipping' => $this->date,
            'car' => $this->car ?? $this->trailer,
            'driver' => $this->driver,
            'loading_warehouse' => optional($this->loadingZone()->first())->name,
            'violations' => $this->violations,
            'weight' => $this->weight,
            'curr_temp' => (integer) optional($actionGeofences->last())->temp,
            'points_total' => $this->retailOutlets->count() + 1,
            'points_completed' => 0,
        ];
    }
}
