<?php

namespace App\Http\Resources;

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
        $actionGeofences = $this->wialonNotifications->where('action_type', WialonNotification::ACTION_GEOFENCE)
            ->map(function ($n) {
                return $n->actionGeofences;
            })
            ->flatten(1)
            ->sortBy('created_at');

        $curr_temp = optional($actionGeofences->last())->temp;

        return [
            'id' => $this->id,
            'date_shipping' => $this->date,
            'car' => $this->car ?? $this->trailer,
            'driver' => $this->driver,
            'loading_warehouse' => optional($this->loadingZone()->first())->name,
            'violations' => ViolationResource::collection($this->violations),
            'weight' => $this->weight,
            'curr_temp' => !empty($curr_temp) ? (integer) $curr_temp : '?',
            'points_total' => $this->retailOutlets->count() + 1,
            'points_completed' => $actionGeofences->where('is_entrance', true)->count(),
        ];
    }
}
