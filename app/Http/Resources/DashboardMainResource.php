<?php

namespace App\Http\Resources;

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
        $loading_warehouse = optional($this->loadingZones()
                                    ->first())->name;

        return [
            'id' => $this->id,
            'date_shipping' => $this->date,
            'car' => $this->car ?? $this->trailer,
            'driver' => $this->driver,
            'loading_warehouse' => $loading_warehouse,
            'weight' => $this->weight
        ];
    }
}
