<?php

namespace App\Models\ShipmentList;

use App\Filters\Filterable;
use App\Models\BaseModel;
use App\Models\LoadingZone;
use App\Models\Violation;
use App\Models\Wialon\Action\ActionWialonGeofence;
use App\Models\Wialon\WialonGeofence;
use App\Models\Wialon\WialonNotification;
use Illuminate\Support\Carbon;
use App\Models\LoadingZoneShipment;

class Shipment extends BaseModel
{
    use Filterable;

    public $incrementing = false;
    protected $keyType = 'string';

    const STATUS_DELETE = '-1';
    const STATUS_CREATE = '0';
    const STATUS_UPDATE = '1';

    const MARK_OWN = '0';
    const MARK_HIRED = '1';
    const MARK_OWN_STR = 'собственный';
    const MARK_HIRED_STR = 'наемный';

    const ENUM_STATUS = [self::STATUS_DELETE, self::STATUS_CREATE, self::STATUS_UPDATE];
    const ENUM_MARK = [self::MARK_OWN, self::MARK_HIRED];
    const ENUM_MARK_STR = [self::MARK_OWN, self::MARK_HIRED];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'status',
        'timestamp',
        'w_conn_id',
        'date',
        'time',
        'carrier',
        'car',
        'trailer',
        'weight',
        'mark',
        'driver',
        'phone',
        'temperature',
        'completed',
        'not_completed',
        'created_at',
        'stock',
    ];

    protected $dates = [
        'date',
        'time',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'temperature' => 'array',
        'stock' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function shipmentRetailOutlets()
    {
        return $this->belongsToMany(ShipmentRetailOutlet::class)->using(ShipmentShipmentRetailOutlet::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function loadingZones()
    {
        return $this->belongsToMany(LoadingZone::class)->using(LoadingZoneShipment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function violations()
    {
        return $this->hasMany(Violation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wialonNotifications()
    {
        return $this->hasMany(WialonNotification::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wialonGeofences()
    {
        return $this->hasMany(WialonGeofence::class);
    }

    /**
     * @param $query
     * @param array $idTypes
     * @return mixed
     */
    public function scopeOfStockId($query, array $idTypes = []) {
        return $query->when(
            isset($idTypes['id_1c']) && !empty($idTypes['id_1c']),
            function ($query) use ($idTypes) {
                $query->whereNotNull('stock->id_1c')->where('stock->id_1c', $idTypes['id_1c']);
            },
            function ($query) use ($idTypes) {
                $query->whereNotNull('stock->id_sap')->where('stock->id_sap', $idTypes['id_sap']);
            }
        );
    }

    /**
     * @param ENUM_MARK_STR $mark
     */
    public static function markToBoolean (string $mark) {
        return \Str::is(self::MARK_OWN, $mark);
    }

    /**
     * @param ENUM_MARK_STR $mark
     */
    public static function markToString (string $mark) {
        return \Str::is(self::MARK_OWN, $mark) ? self::MARK_OWN_STR : self::MARK_HIRED_STR;
    }

    public function getLastArriveDateAttribute ($value) {
        return Carbon::parse(
            $this->date->format('d.m.Y').' '.
            $this->time->format('H:i')
        );
    }

}
