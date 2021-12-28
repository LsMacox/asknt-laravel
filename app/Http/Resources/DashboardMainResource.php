<?php

namespace App\Http\Resources;

use App\Repositories\LoadingZoneRepository;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardMainResource extends JsonResource
{

    private $loadingZoneRepository;

    /**
     * RetailOutletsController constructor.
     * @param  mixed  $resource
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->loadingZoneRepository = app(LoadingZoneRepository::class);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $loading_warehouse = optional($this->loadingZoneRepository
                                    ->builderByIdSapOr1c($this->stock['idsap'], $this->stock['id1c'])
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
