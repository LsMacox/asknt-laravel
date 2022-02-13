<?php

namespace App\Http\Resources;

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
        $actionGeofenceEntrance = $this->actionWialonGeofences()->where('is_entrance', true)->first();
        $actionGeofenceDeparture = $this->actionWialonGeofences()->where('is_entrance', false)->first();

        $actionGeofenceDoorOpen = $this->actionWialonGeofences()->where('is_entrance', true)
            ->where('door', ActionWialonGeofence::DOOR_OPEN)->first();
        $actionGeofenceDoorClose = $this->actionWialonGeofences()->where('is_entrance', false)
            ->where('door', ActionWialonGeofence::DOOR_CLOSE)->first();

        if (method_exists($this->resource, 'shipmentRetailOutlet')) {
            $shipmentRetailOutlets = $this->shipmentRetailOutlet()->first();
        }

        $timeOnPoint = '';
        $doorOpen = '';

        if ($actionGeofenceEntrance && $actionGeofenceDeparture) {
            $timeOnPoint = $this->getTimeBetween($actionGeofenceEntrance->created_at, $actionGeofenceDeparture->created_at);
        }

        if ($actionGeofenceDoorOpen && $actionGeofenceDoorClose) {
            $doorOpen = $this->getTimeBetween($actionGeofenceDoorOpen->created_at, $actionGeofenceDoorClose->created_at);
        }

        $arrive_from_actual = $actionGeofenceEntrance ? Carbon::parse($actionGeofenceEntrance->created_at)->format('H:i') : null;
        $arrive_to_actual = $actionGeofenceDeparture ? Carbon::parse($actionGeofenceDeparture->created_at)->format('H:i') : null;
        $arrive_from_plan = isset($shipmentRetailOutlets) && $shipmentRetailOutlets->arrive_from ?
            Carbon::parse($shipmentRetailOutlets->arrive_from)->format('H:i') : null;
        $arrive_to_plan = isset($shipmentRetailOutlets) && $shipmentRetailOutlets->arrive_to ?
            Carbon::parse($shipmentRetailOutlets->arrive_to)->format('H:i') : null;
        $late = false;

        if ($arrive_from_actual && $arrive_from_plan && $arrive_to_plan) {
            $planStart = Carbon::parse($arrive_from_plan);
            $planFinish = Carbon::parse($arrive_to_plan);
            $actualFinish = Carbon::parse($arrive_from_actual);

            $late = !$actualFinish->between($planStart, $planFinish);
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
            'arrive_from_plan' => $arrive_from_plan,
            'arrive_to_plan' => $arrive_to_plan,
            'arrive_from_actual' => $arrive_from_actual,
            'arrive_to_actual' => $arrive_to_actual,
            'time_on_point' => $timeOnPoint,
            'passed' => !!$actionGeofenceEntrance,
            'late' => $late,
            'door_open' => $doorOpen,
        ];
    }

    /**
     * @param $time1
     * @param $time2
     * @return string
     */
    public function getTimeBetween($time1, $time2): string
    {
        $timeStr = '';

        $date1 = Carbon::parse($time1);
        $date2 = Carbon::parse($time2);

        $diffInHours = $date2->diffInHours($date1);
        $diffInMins = $date2->diffInMinutes($date1);
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
