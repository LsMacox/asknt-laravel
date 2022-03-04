<?php

namespace App\Http\Resources;

use App\Models\Wialon\Action\ActionWialonGeofence;
use App\Models\Wialon\WialonNotification;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class DashboardDetailResource extends JsonResource
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

        $notificationTemps = $this->wialonNotifications->where('action_type', WialonNotification::ACTION_TEMP)->first();

        $temps = null;
        $curr_temp = null;
        $avg_temp = null;

        if ($notificationTemps) {
            $actionsTemps = $notificationTemps
                            ->actionTemps
                            ->sortBy('created_at');

            $temps = $actionsTemps->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y.m.d');
            })->map(function ($group) {
                return $group->mapWithKeys(function ($at) {
                    $time = $at->created_at->format('H:i');
                    return [$time => $at->temp];
                });
            })->sort();

            $curr_temp = optional($actionsTemps->last())->temp;
            $avg_temp = $actionsTemps->avg('temp');
        }

        return [
            'id' => $this->id,
            'car' => $this->car,
            'carrier' => $this->carrier,
            'trailer' => $this->trailer,
            'driver' => $this->driver,
            'phone' => $this->phone,
            'loading_zone' => new MorePointResource($this->loadingZones->first()),
            'retail_outlets' => MorePointResource::collection($this->retailOutlets->sortBy('turn')),
            'stock' => $this->stock,
            'temps' => $temps,
            'temperature' => $this->temperature,
            'duration' => MorePointResource::getTimeBetween(
                optional($actionGeofences->where('is_entrance', false)->first())->created_at,
                optional($actionGeofences->last())->created_at
            ),
            'mileage' => optional($actionGeofences)->sum('mileage'),
            'curr_temp' => !empty($curr_temp) ? (integer) $curr_temp : '?',
            'avg_temp' => !empty($avg_temp) ? (integer) $avg_temp : '?',
            'weight' => $this->weight,
        ];
    }
}
