<?php

namespace App\Avantern;

use App\Avantern\Dto\DataDto;
use App\Avantern\Dto\StockDto;
use App\Avantern\Dto\TemperatureDto;

/**
 * Class SoapShipmentServer
 */
class SoapShipmentServer
{

    /**
     * saving data
     * @param string $system
     * @param DataDto $dataDto
     * @param TemperatureDto $temperatureDto
     * @param StockDto $stockDto
     *
     */
    public function saveAvanternShipment(
        string $system,
        DataDto $dataDto,
        TemperatureDto $temperatureDto,
        StockDto $stockDto
    )
    {
        Log::debug('Completed: ' + $system);
    }
}
