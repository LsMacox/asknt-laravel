<?php

namespace App\Models\ShipmentList;

use App\Models\BaseModel;
use App\Models\RetailOutlet;
use App\Models\Wialon\Action\ActionWialonGeofence;
use App\Models\Wialon\WialonGeofence;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class ShipmentRetailOutlet extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'code',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function shipments()
    {
        return $this->belongsToMany(Shipment::class)->using(ShipmentShipmentRetailOutlet::class);
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
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function actionWialonGeofences()
    {
        return $this->morphMany(ActionWialonGeofence::class, 'pointable');
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
