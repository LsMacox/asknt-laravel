<?php

namespace App\Models\Wialon\Action;

use App\Models\BaseModel;
use App\Models\Wialon\WialonNotification;

class ActionWialonTemp extends BaseModel
{

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
        'created_at',
        'updated_at'
    ];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wialonNotification()
    {
        return $this->belongsTo(WialonNotification::class);
    }

    /**
     * @param $value
     * @return string
     */
    public function getTempAttribute($value) {
        $value = \Str::of($value)
            ->remove('Средняя температура iQF:');

        $value = (string) $value
            ->replaceMatches('/('.implode('|',ActionWialonTempViolation::ENUM_TEMP).')/', '')
            ->trim();

        return $value;
    }


}
