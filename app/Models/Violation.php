<?php

namespace App\Models;

use App\Models\ShipmentList\Shipment;

class Violation extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'shipment_id',
        'text',
        'repaid',
        'repaid_description',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

}
