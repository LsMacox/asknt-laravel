<?php

namespace App\Models\Wialon;

use App\Models\BaseModel;

class WialonObjects extends BaseModel
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'w_id',
        'name',
        'registration_plate',
        'w_conn_id',
    ];

}
