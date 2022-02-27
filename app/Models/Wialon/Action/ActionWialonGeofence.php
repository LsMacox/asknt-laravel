<?php

namespace App\Models\Wialon\Action;

use App\Models\BaseModel;
use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\WialonNotification;

class ActionWialonGeofence extends BaseModel
{

    const DOOR_OPEN = 'открыта';
    const DOOR_CLOSE = 'закрыта';

    const ENUM_DOOR = [self::DOOR_OPEN, self::DOOR_CLOSE];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'shipment_id',
        'wialon_notification_id',
        'point_id',
        'point_type',
        'name',
        'temp',
        'temp_type',
        'door',
        'lat',
        'long',
        'mileage',
        'is_entrance',
        'created_at',
        'updated_at',
    ];

    protected $dates = ['created_at', 'updated_at'];

    protected $casts = [
        'temp' => 'double',
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
    public function wialonNotification()
    {
        return $this->belongsTo(WialonNotification::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function pointable()
    {
        return $this->morphTo();
    }

}
