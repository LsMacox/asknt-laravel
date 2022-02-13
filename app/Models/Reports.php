<?php

namespace App\Models;

use App\Models\BaseModel;

class Reports extends BaseModel
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
