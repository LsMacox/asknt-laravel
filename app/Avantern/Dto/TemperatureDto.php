<?php

namespace App\Avantern\Dto;


/**
 * Class TemperatureDto
 * @package App\Avantern\Dto
 */
class TemperatureDto
{
    /**
     * @var int
     */
    public $from;

    /**
     * @var int
     */
    public $to;


    /**
     * DataDto constructor.
     * @param int $from
     * @param int $to
     */
    public function __construct(
        int $from,
        int $to
    )
    {
        $this->from = $from;
        $this->to = $to;
    }
}
