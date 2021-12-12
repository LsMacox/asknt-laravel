<?php

namespace App\Models\ShipmentList;

use App\Models\BaseModel;

class ShipmentOrders extends BaseModel
{


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'product',
        'weight',
    ];

}
