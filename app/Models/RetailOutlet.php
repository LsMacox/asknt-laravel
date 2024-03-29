<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Filters\Filterable;
use App\Models\ShipmentList\Shipment;
use App\Models\ShipmentList\ShipmentOrder;
use App\Models\ShipmentList\ShipmentRetailOutlet;
use App\Models\Wialon\Action\ActionWialonGeofence;
use Illuminate\Database\Eloquent\SoftDeletes;

class RetailOutlet extends BaseModel
{

    use Filterable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'shipment_retail_outlet_id',
        'code',
        'address',
        'shipment_id',
        'lng',
        'lat',
        'turn',
        'radius'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function shipmentRetailOutlet() {
        return $this->hasOne(ShipmentRetailOutlet::class, 'id', 'shipment_retail_outlet_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shipmentOrders() {
        return $this->hasMany(ShipmentOrder::class, 'shipment_retail_outlet_id', 'shipment_retail_outlet_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function actionWialonGeofences()
    {
        return $this->morphMany(ActionWialonGeofence::class, 'pointable');
    }

}
