<?php

namespace App\Filters;

use Illuminate\Support\Carbon;

class ShipmentFilter extends QueryFilter
{

    /**
     * @param string $str
     */
    public function filterCar($str)
    {
        $this->builder->where('car', $str);
    }

    /**
     * @param string $str
     */
    public function filterStockName($str)
    {
        $this->builder->where('stock->name', $str);
    }

    /**
     * @param string $str
     */
    public function filterCarrier($str)
    {
        $this->builder->where('carrier', $str);
    }

    /**
     * @param string $str
     */
    public function filterWeight($str)
    {
        $this->builder->where('weight', $str);
    }

    /**
     * @param string $str
     */
    public function filterDriver($str)
    {
        $this->builder->where('driver', $str);
    }

    /**
     * @param integer $num
     */
    public function filterRoute($num)
    {
        $this->builder->where('id', $num);
    }

    public function filterDateStart () {
        $this->builder->whereBetween(
            'created_at',
            [
                Carbon::parse($this->fields()['filter']['date_start']),
                Carbon::parse($this->fields()['filter']['date_end'])
            ]
        );
    }
}
