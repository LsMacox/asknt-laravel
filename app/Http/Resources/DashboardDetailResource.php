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

        $todayGeofences = $actionGeofences->where('created_at', '>=', date('Y-m-d').' 00:00:00')
                                            ->where('created_at', '<=', date('Y-m-d').' 24:59:59');

        $todayTemps = [];
        $todayGeofences->each(function ($tg) use (&$todayTemps) {
            $time = $tg->created_at->format('H:i');
            $todayTemps[$time] = $tg->temp;
        })->toArray();

        $curr_temp = optional($actionGeofences->last())->temp;
        $avg_temp = $actionGeofences->avg('temp');

        return [
            'id' => $this->id,
            'car' => $this->car,
            'carrier' => $this->carrier,
            'trailer' => $this->trailer,
            'driver' => $this->driver,
            'phone' => $this->phone,
            'loading_zone' => new MorePointResource($this->loadingZone),
            'retail_outlets' => MorePointResource::collection($this->retailOutlets->sortBy('turn')),
            'stock' => $this->stock,
            'today_temps' => $todayTemps,
            'temperature' => $this->temperature,
            'duration' => MorePointResource::getTimeBetween(
                optional($actionGeofences->where('is_entrance', false)->first())->created_at,
                optional($actionGeofences->last())->created_at
            ),
            'mileage' => optional($actionGeofences->last())->mileage,
            'curr_temp' => !empty($curr_temp) ? (integer) $curr_temp : '?',
            'avg_temp' => !empty($avg_temp) ? (integer) $avg_temp : '?',
            'weight' => $this->weight,
        ];
    }
}
