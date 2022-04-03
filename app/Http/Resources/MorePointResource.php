<?php

namespace App\Http\Resources;

use App\Models\LoadingZone;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use App\Models\Wialon\Action\ActionWialonGeofence;
use Illuminate\Http\Resources\Json\JsonResource;

class MorePointResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $entrance = null;
        $departure = null;
        if ($this->actGeofences) {
            $entrance = $this->actGeofences->where('is_entrance', true)
                ->where('pointable_type', $this->resource->getMorphClass())
                ->where('pointable_id', $this->id)->first();
            $departure = $this->actGeofences->where('is_entrance', false)
                ->where('pointable_type', $this->resource->getMorphClass())
                ->where('pointable_id', $this->id)->first();
        }

        $time_on_point = '';
        $door_open = $departure ? $departure->door : optional($entrance)->door;
        $actual_start = optional($entrance)->created_at;
        $actual_finish = optional($departure)->created_at;
        $late = false;

        if ($entrance && $departure) {
            $time_on_point = $this->getTimeBetween($entrance->created_at, $departure->created_at);
        }

        if ($this->resource instanceof ShipmentRetailOutlet) {
            if ($actual_start) {
                $late = !($actual_start->gt($this->planStart) && $actual_start->lt($this->planFinish));
            }
        }

        return [
            'id' => $this->id,
            'code' => $this->code,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'name' => $this->name,
            'radius' => $this->radius,
            'shipment_orders' => $this->shipmentOrders,
            'turn' => $this->turn,
            'temp_from' => optional($entrance)->temp,
            'temp_to' => optional($departure)->temp,
            'arrive_from_plan' => optional($this->planStart)->format('H:i'),
            'arrive_to_plan' => optional($this->planFinish)->format('H:i'),
            'arrive_from_actual' => optional($actual_start)->format('H:i'),
            'arrive_to_actual' => optional($actual_finish)->format('H:i'),
            'time_on_point' => $time_on_point,
            'passed' => optional($departure)->pointable_type === LoadingZone::getMorphClass()
                ? !!$departure
                : !!$entrance,
            'late' => $late,
            'door_open' => !is_null($door_open) ? $door_open === ActionWialonGeofence::DOOR_OPEN : null,
        ];
    }

    /**
     * @param $time1
     * @param $time2
     * @return string|null
     */
    public function getTimeBetween($time1, $time2)
    {
        if (empty($time1) && empty($time2)) return null;

        $timeStr = '';

        $diffInHours = $time2->diffInHours($time1);
        $diffInMins = $time2->diffInMinutes($time1);
        $diffInMins = $diffInMins - ($diffInHours * 60);

        $hoursTrans = trans_choice('час|часа|часов', $diffInHours);
        $minsTrans = trans_choice('минута|минуты|минут', $diffInMins);

        if ($diffInHours > 0) {
            $timeStr .= $diffInHours . ' ' . $hoursTrans . ' ' . $diffInMins . ' ' . $minsTrans;
        } else if ($diffInMins > 0) {
            $timeStr .= $diffInMins . ' ' . $minsTrans;
        }
        return $timeStr;
    }

}
