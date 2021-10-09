<?php

namespace App\Models;

use App\Models\BaseModel;

class LoadingZone extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'id_sap',
        'id_1c',
        'lng',
        'lat',
        'radius',
    ];

}
