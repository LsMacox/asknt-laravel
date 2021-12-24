<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Filters\Filterable;
use App\Models\Wialon\WialonGeofence;

class RetailOutlet extends BaseModel
{

    use Filterable;

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
