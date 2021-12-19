<?php

namespace App\Models\ShipmentList;

use App\Models\BaseModel;
use App\Models\ShipmentList\ShipmentOrders;

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

}
