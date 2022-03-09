<?php

namespace App\Http\Resources;

use App\Models\LoadingZone;
use App\Models\RetailOutlet;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use App\Models\Wialon\Action\ActionWialonGeofence;
use App\Models\Wialon\WialonNotification;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

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
        $shipment = $this->shipment ?? $this->shipments->first();

        $actionGeofenceEntrance = $this->actionWialonGeofences->where('is_entrance', true)->first();
        $actionGeofenceDeparture = $this->actionWialonGeofences->where('is_entrance', false)->first();

        $retailOutletTurn = $this->turn;
        $actionGeofenceDoorOpen = $shipment->actionGeofences()
            ->where('door', ActionWialonGeofence::DOOR_OPEN)
            ->when($retailOutletTurn, function ($query, $turn) {
                $query->whereHasMorph('pointable', [RetailOutlet::class], function ($subQuery) use ($turn) {
                    $subQuery->where('turn', '>=', $turn);
                });
            })->get()->last();

        $actionGeofenceDoorClose = $actionGeofenceEntrance ?? $actionGeofenceDeparture;

        if (!$actionGeofenceDoorOpen) {
            $actionGeofenceDoorOpen = $shipment->actionGeofences->where('door', ActionWialonGeofence::DOOR_OPEN)->last();
        }

        $timeOnPoint = '';
        $doorOpen = '';
        $actualStart = optional($actionGeofenceEntrance)->created_at;
        $actualFinish = optional($actionGeofenceDeparture)->created_at;
        $planStart = null;
        $planFinish = null;
        $late = false;

        if ($actionGeofenceEntrance && $actionGeofenceDeparture) {
            $timeOnPoint = $this->getTimeBetween($actionGeofenceEntrance->created_at, $actionGeofenceDeparture->created_at);
        }

        if ($this->resource instanceof ShipmentRetailOutlet &&
            $actionGeofenceDoorOpen && $actionGeofenceDoorClose) {
            $doorOpen = $this->getTimeBetween($actionGeofenceDoorOpen->created_at, $actionGeofenceDoorClose->created_at);

            $planStart = $this->shipmentRetailOutlet->planStart;
            $planFinish = $this->shipmentRetailOutlet->planFinish;

            if ($actualStart && $this->shipmentRetailOutlet) {
                $late = !($actualStart->gt($planStart) && $actualStart->lt($planFinish));
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
            'temp_from' => optional($actionGeofenceEntrance)->temp,
            'temp_to' => optional($actionGeofenceDeparture)->temp,
            'arrive_from_plan' => optional($planStart)->format('H:i'),
            'arrive_to_plan' => optional($planFinish)->format('H:i'),
            'arrive_from_actual' => optional($actualStart)->format('H:i'),
            'arrive_to_actual' => optional($actualFinish)->format('H:i'),
            'time_on_point' => $timeOnPoint,
            'passed' => optional($actionGeofenceDeparture)->pointable_type === LoadingZone::getMorphClass()
                ? !!$actionGeofenceDeparture
                : !!$actionGeofenceEntrance,
            'late' => $late,
            'door_open' => $doorOpen,
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
