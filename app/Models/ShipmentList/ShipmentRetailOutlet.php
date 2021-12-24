<?php

namespace App\Models\ShipmentList;

use App\Models\BaseModel;
use App\Models\ShipmentList\ShipmentOrders;
use App\Models\Wialon\WialonGeofence;

class ShipmentRetailOutlet extends BaseModel
{


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
    public function orders() {
        return $this->hasMany(ShipmentOrders::class);
    }

    /**
     * Get all of the wialon geofences' comments.
     */
    public function wialonGeofences()
    {
        return $this->morphMany(WialonGeofence::class, 'geofenceable');
    }

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
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleted(function ($model) {
            $model->wialonGeofences()->delete();
        });
    }

}
