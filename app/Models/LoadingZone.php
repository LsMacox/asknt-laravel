<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Filters\Filterable;

class LoadingZone extends BaseModel
{

    use Filterable;

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
