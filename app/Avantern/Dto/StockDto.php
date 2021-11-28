<?php

namespace App\Avantern\Dto;


/**
 * Class StockDto
 * @package App\Avantern\Dto
 */
class StockDto
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $id1c;

    /**
     * @var string
     */
    public $idsap;

    /**
     * @var string
     */
    public $time;


    /**
     * StockDto constructor.
     * @param string $name
     * @param string $id1c
     * @param string $idsap
     * @param string $time
     */
    public function __construct(
        string $name,
        string $id1c,
        string $idsap,
        string $time
    )
    {
        $this->name = $name;
        $this->id1c = $id1c;
        $this->idsap = $idsap;
        $this->time = $time;
    }
}
