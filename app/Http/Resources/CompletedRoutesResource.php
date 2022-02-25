<?php

namespace App\Http\Resources;

use App\Models\Wialon\WialonNotification;
use Illuminate\Http\Resources\Json\JsonResource;

class CompletedRoutesResource extends JsonResource
{

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'shipment_id' => $this->id,
            'date_shipping' => $this->date,
            'car' => $this->car ?? $this->trailer,
            'carrier' => $this->carrier,
            'loading_warehouse' => optional($this->loadingZone()->first())->name,
            'created_at' => $this->created_at,
        ];
    }
}
