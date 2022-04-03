<?php

namespace App\Http\Resources;

use App\Facades\ShipmentDataService;
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

        $temps = null;
        $curr_temp = null;
        $avg_temp = null;

        if ($actTemps) {
            $temps = $actTemps->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y.m.d');
            })->map(function ($group) {
                return $group->mapWithKeys(function ($at) {
                    $time = $at->created_at->format('H:i');
                    return [$time => $at->temp];
                });
            })->sort();

            $curr_temp = optional($actTemps->last())->temp;
            $avg_temp = $actTemps->avg('temp');
        }

        $loadingZone = $this->loadingZones->first();
        $retailOutlets = $this->shipmentRetailOutlets->sortBy('turn');

        $loadingZone->actGeofences = $actGeofences;
        $retailOutlets = $retailOutlets->map(function ($retailOutlet) use ($actGeofences) {
            $retailOutlet->actGeofences = $actGeofences;
            return $retailOutlet;
        });

        return [
            'id' => $this->id,
            'car' => $this->car,
            'carrier' => $this->carrier,
            'trailer' => $this->trailer,
            'driver' => $this->driver,
            'phone' => $this->phone,
            'loading_zone' => new MorePointResource($loadingZone),
            'retail_outlets' => MorePointResource::collection($retailOutlets),
            'stock' => $this->stock,
            'temps' => $temps,
            'temperature' => $this->temperature,
            'duration' => MorePointResource::getTimeBetween(
                $actGeofences ? optional($actGeofences->last())->created_at : '',
                $actGeofences ? optional($actGeofences->first())->created_at : ''
            ),
            'mileage' => $actGeofences ? optional($actGeofences->first())->mileage : 0,
            'curr_temp' => !!$curr_temp ? round($curr_temp, 1) : '?',
            'avg_temp' => !!$avg_temp ? round($avg_temp, 1) : '?',
            'points_total' => $this->shipmentRetailOutlets->count() + 1,
            'points_completed' => $actGeofences ? $actGeofences->where('is_entrance', true)->count() : '',
            'weight' => $this->weight,
        ];
    }
}
