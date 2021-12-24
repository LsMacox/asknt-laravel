<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Filters\Filterable;
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
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleted(function ($model) {
            $model->wialonGeofences()->delete();
        });
    }

}
