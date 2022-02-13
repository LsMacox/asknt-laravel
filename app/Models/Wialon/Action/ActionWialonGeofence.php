<?php

namespace App\Models\Wialon\Action;

use App\Models\BaseModel;
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
        'wialon_notification_id',
        'name',
        'temp',
        'temp_type',
        'door_type',
        'lat',
        'long',
        'is_entrance',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'temp' => 'double',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wialonNotification()
    {
        return $this->belongsTo(WialonNotification::class);
    }

}
