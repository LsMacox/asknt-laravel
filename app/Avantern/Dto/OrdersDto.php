<?php

namespace App\Avantern\Dto;


/**
 * Class OrdersDto
 * @package App\Avantern\Dto
 */
class OrdersDto
{
    /**
     * @var int
     */
    public $order;

    /**
     * @var string
     */
    public $product;

    /**
     * @var float
     */
    public $weight;


    /**
     * OrdersDto constructor.
     * @param int $order
     * @param string $product
     * @param float $weight
     */
    public function __construct(
        int $order,
        string $product,
        float $weight
    )
    {
        $this->order = $order;
        $this->product = $product;
        $this->weight = $weight;
    }
}
