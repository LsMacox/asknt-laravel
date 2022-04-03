<?php

namespace App\Models\ShipmentList;

use App\Models\BaseModel;
use App\Models\RetailOutlet;

class ShipmentOrder extends BaseModel
{
    const RETURN_1 = '1';
    const RETURN_2 = '0';
    const ENUM_RETURN = [self::RETURN_1, self::RETURN_2];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function shipmentRetailOutlets() {
        return $this->belongsToMany(ShipmentRetailOutlet::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function retailOutlet() {
        return $this->belongsTo(RetailOutlet::class);
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'code',
        'product',
        'return',
        'weight',
    ];

    /**
     * @param ENUM_RETURN_STR $return
     */
    public static function returnToBoolean (string $return) {
        return \Str::is(self::ENUM_RETURN, $return);
    }

}
