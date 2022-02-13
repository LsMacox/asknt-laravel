<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Filters\Filterable;
use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\Action\ActionWialonGeofence;
use App\Models\Wialon\WialonGeofence;

class LoadingZone extends BaseModel
{

    use Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'id_sap',
        'shipment_id',
        'id_1c',
        'lng',
        'lat',
        'radius',
    ];

    /**
     * Get all of the wialon geofences' comments.
     */
    public function wialonGeofences()
    {
        return $this->morphMany(WialonGeofence::class, 'geofenceable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function actionWialonGeofences()
    {
        return $this->morphMany(ActionWialonGeofence::class, 'pointable');
    }


}
