<?php

namespace App\Avantern\Dto;


/**
 * Class ScoresDto
 * @package App\Avantern\Dto
 */
class ScoresDto
{
    /**
     * @var int
     */
    public $score;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $legal_name;

    /**
     * @var string
     */
    public $address;

    /**
     * @var float
     */
    public $long;

    /**
     * @var float
     */
    public $lat;

    /**
     * @var string
     */
    public $date;

    /**
     * @var string
     */
    public $arrive;

    /**
     * @var string
     */
    public $leave;

    /**
     * @var int
     */
    public $turn;

    /**
     * @var App\Avantern\Dto\OrdersDto[]
     */
    public $orders;


    /**
     * ScoresDto constructor.
     * @param int $score
     * @param string $name
     * @param string $legal_name
     * @param string $address
     * @param float $long
     * @param float $lat
     * @param string $date
     * @param string $arrive
     * @param string $leave
     * @param int $turn
     * @param OrdersDto[] $orders
     */
    public function __construct(
        int $score,
        string $name,
        string $legal_name,
        string $address,
        float $long,
        float $lat,
        string $date,
        string $arrive,
        string $leave,
        int $turn,
        array $orders
    )
    {
        $this->score = $score;
        $this->name = $name;
        $this->legal_name = $legal_name;
        $this->address = $address;
        $this->long = $long;
        $this->lat = $lat;
        $this->date = $date;
        $this->arrive = $arrive;
        $this->leave = $leave;
        $this->turn = $turn;
        $this->orders = $orders;
    }
}
