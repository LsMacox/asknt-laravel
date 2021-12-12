<?php

namespace App\Models\ShipmentList;

use App\Models\BaseModel;

class ShipmentRetailOutlet extends BaseModel
{


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'legal_name',
        'adres',
        'long',
        'lat',
        'date',
        'arrive_from',
        'arrive_to',
        'turn',
    ];

}
