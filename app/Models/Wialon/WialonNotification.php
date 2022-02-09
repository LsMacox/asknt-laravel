<?php

namespace App\Models\Wialon;

use App\Models\BaseModel;
use App\Models\ShipmentList\Shipment;

class WialonNotification extends BaseModel
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'object_id',
        'shipment_id',
        'created_at',
        'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

}
