<?php

namespace App\Models\Wialon;

use App\Models\BaseModel;
use App\Models\ShipmentList\Shipment;
use Illuminate\Database\Eloquent\SoftDeletes;

class WialonGeofence extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'w_conn_id',
        'shipment_id',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

}
