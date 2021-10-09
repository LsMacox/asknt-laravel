<?php

namespace App\Models\Wialon;

use App\Models\BaseModel;

class WialonReports extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'car_name',
        'transport_comp',
        'shipping_warehouse',
    ];

}
