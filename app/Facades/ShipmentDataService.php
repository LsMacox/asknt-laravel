<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class ShipmentDataService
 * @package App\Facades
 */
class ShipmentDataService extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'shipment-data-service';
    }
}
