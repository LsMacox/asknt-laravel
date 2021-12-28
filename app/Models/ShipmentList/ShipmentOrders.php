<?php

namespace App\Models\ShipmentList;

use App\Models\BaseModel;
use Str;
use App\Models\ShipmentList\ShipmentRetailOutlet;

class ShipmentOrders extends BaseModel
{


    const RETURN_1_STR = 'возврат';
    const RETURN_2_STR = 'не возврат';
    const ENUM_RETURN_STR = [self::RETURN_1_STR, self::RETURN_2_STR];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shipmentRetailOutlet () {
        return $this->belongsTo(ShipmentRetailOutlet::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'shipment_retail_outlet_id',
        'product',
        'return',
        'weight',
    ];

    /**
     * @param ENUM_RETURN_STR $return
     */
    public static function returnToBoolean (string $return) {
        $lReturn = Str::lower($return);
        return Str::is(self::RETURN_1_STR, $lReturn);
    }

}
