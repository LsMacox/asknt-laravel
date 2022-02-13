<?php

namespace App\Models\Wialon;

use App\Models\BaseModel;
use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\Action\ActionWialonGeofence;
use App\Models\Wialon\Action\ActionWialonTempViolation;

class WialonNotification extends BaseModel
{

    const ACTION_GEOFENCE = 'geofence';
    const ACTION_TEMP_VIOLATION = 'temp_violation';

    const ENUM_ACTION = [self::ACTION_GEOFENCE, self::ACTION_TEMP_VIOLATION];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'object_id',
        'action_type',
        'shipment_id',
        'created_at',
        'updated_at',
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
    public function actionGeofences()
    {
        return $this->hasMany(ActionWialonGeofence::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actionTempViolations()
    {
        return $this->hasMany(ActionWialonTempViolation::class);
    }


}
