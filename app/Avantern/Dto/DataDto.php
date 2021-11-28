<?php

namespace App\Avantern\Dto;


/**
 * Class DataDto
 * @package App\Avantern\Dto
 */
class DataDto
{
    /**
     * @var int
     */
    public $number;

    /**
     * @var int
     */
    public $status;

    /**
     * @var int
     */
    public $timestamp;

    /**
     * @var string
     */
    public $date;

    /**
     * @var string
     */
    public $time;

    /**
     * @var string
     */
    public $carrier;

    /**
     * @var string
     */
    public $car;

    /**
     * @var string
     */
    public $trailer;

    /**
     * @var string
     */
    public $weight;

    /**
     * @var int
     */
    public $mark;

    /**
     * @var string
     */
    public $driver;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var App\Avantern\Dto\ScoresDto[]
     */
    public $scoresDto;


    /**
     * DataDto constructor.
     * @param int $number
     * @param int $status
     * @param int $timestamp
     * @param string $date
     * @param string $carrier
     * @param string $car
     * @param string $trailer
     * @param string $weight
     * @param int $mark
     * @param string $phone
     * @param ScoresDto[] $scoresDto
     */
    public function __construct(
        int $number,
        int $status,
        int $timestamp,
        string $date,
        string $carrier,
        string $car,
        string $trailer,
        string $weight,
        int $mark,
        string $phone,
        array $scoresDto
    )
    {
        $this->number = $number;
        $this->status = $status;
        $this->timestamp = $timestamp;
        $this->date = $date;
        $this->carrier = $carrier;
        $this->car = $car;
        $this->trailer = $trailer;
        $this->weight = $weight;
        $this->mark = $mark;
        $this->phone = $phone;
        $this->scoresDto = $scoresDto;
    }
}
