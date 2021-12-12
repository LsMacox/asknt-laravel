<?php

namespace App\Models\ShipmentList;

use App\Models\BaseModel;

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

}
