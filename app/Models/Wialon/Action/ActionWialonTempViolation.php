<?php

namespace App\Models\Wialon\Action;

use App\Models\BaseModel;
use App\Models\Wialon\WialonNotification;

class ActionWialonTempViolation extends BaseModel
{

    const TEMP_CELSIUS = '°C';
    const TEMP_FAHRENHEIT = '°F';

    const ENUM_TEMP = [self::TEMP_CELSIUS, self::TEMP_FAHRENHEIT];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'wialon_notification_id',
        'temp',
        'temp_type',
        'lat',
        'long',
        'created_at',
        'updated_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wialonNotification()
    {
        return $this->belongsTo(WialonNotification::class);
    }

    public function getTempType(string $str) {
        return (string) \Str::of($str)
            ->match('/('.implode('|',ActionWialonTempViolation::ENUM_TEMP).')/');
    }

}
