<?php

namespace App\Models\ShipmentList;

use App\Models\BaseModel;
use Str;
use App\Models\ShipmentList\ShipmentRetailOutlet;

class Shipment extends BaseModel
{

    const STATUS_DELETE = '-1';
    const STATUS_CREATE = '0';
    const STATUS_UPDATE = '1';

    const MARK_OWN = '0';
    const MARK_HIRED = '1';
    const MARK_OWN_STR = 'собственный';
    const MARK_HIRED_STR = 'наемный';

    const ENUM_STATUS = [self::STATUS_DELETE, self::STATUS_CREATE, self::STATUS_UPDATE];
    const ENUM_MARK = [self::MARK_OWN, self::MARK_HIRED];
    const ENUM_MARK_STR = [self::MARK_OWN_STR, self::MARK_HIRED_STR];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function retailOutlets()
    {
        return $this->hasMany(ShipmentRetailOutlet::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'status',
        'timestamp',
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
        'stock',
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
     * @param ENUM_MARK_STR $mark
     */
    public static function markToBoolean (string $mark) {
        $lMark = Str::lower($mark);
        return Str::is(self::MARK_OWN_STR, $lMark);
    }

}
