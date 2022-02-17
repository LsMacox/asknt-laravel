<?php

namespace App\Models\Wialon;

use App\Models\BaseModel;

class WialonGeofence extends BaseModel
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'w_conn_id',
        'name',
        'geofence_id',
        'geofence_type',
        'created_at',
        'updated_at',
    ];

    /**
     * Get all of the models that own geofences.
     */
    public function geofenceable()
    {
        return $this->morphTo();
    }

}
