<?php

namespace App\Models\Wialon;

use App\Models\BaseModel;

class WialonConnection extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'carrier_code',
        'host',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];
}
