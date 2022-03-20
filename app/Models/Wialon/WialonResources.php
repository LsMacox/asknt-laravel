<?php

namespace App\Models\Wialon;

use App\Models\BaseModel;

class WialonResources extends BaseModel
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
        'w_conn_id',
    ];

}
