<?php

namespace App\Models\ShipmentList;

use App\Models\BaseModel;
use App\Models\RetailOutlet;
use App\Models\ShipmentList\ShipmentOrders;
use App\Models\Wialon\WialonGeofence;
use Illuminate\Support\Carbon;

class ShipmentRetailOutlet extends BaseModel
{

    public $incrementing = false;
    protected $keyType = 'string';

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

    protected $dates = [
        'date',
        'arrive_from',
        'arrive_to'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function retailOutlet() {
        return $this->belongsTo(RetailOutlet::class);
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

    /**
     * @return Carbon
     */
    public function getPlanStartAttribute () {
        return Carbon::parse($this->date->format('d.m.Y') . ' ' . $this->arrive_from->format('H:i'));
    }

    /**
     * @return Carbon
     */
    public function getPlanFinishAttribute () {
        return Carbon::parse($this->date->format('d.m.Y') . ' ' . $this->arrive_to->format('H:i'));
    }

}
