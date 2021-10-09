<?php

namespace App\Models;

use App\Models\BaseModel;

class TradePoint extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'code',
        'address',
        'lng',
        'lat',
        'radius'
    ];

}
