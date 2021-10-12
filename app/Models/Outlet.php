<?php

namespace App\Models;

use App\Models\BaseModel;

class Outlet extends BaseModel
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
