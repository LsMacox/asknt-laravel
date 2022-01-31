<?php

namespace App\Models\ShipmentList;

use App\Models\BaseModel;
use App\Models\ShipmentList\ShipmentOrders;
use App\Models\Wialon\WialonGeofence;

class ShipmentRetailOutlet extends BaseModel
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'shipment_id',
        'legal_name',
        'adres',
        'long',
        'lat',
        'date',
        'arrive_from',
        'arrive_to',
        'turn',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shipmentOrders() {
        return $this->hasMany(ShipmentOrders::class);
    }

    /**
     * Get all of the wialon geofences'.
     */
    public function wialonGeofences()
    {
        return $this->morphMany(WialonGeofence::class, 'geofenceable');
    }

}
