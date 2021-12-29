<?php

namespace App\Filters;

use Illuminate\Support\Carbon;

class ShipmentFilter extends QueryFilter
{

    /**
     * @param string $str
     */
    public function car(string $str)
    {
        $this->builder->where('car', $str);
    }

    /**
     * @param string $str
     */
    public function stockName(string $str)
    {
        $this->builder->where('stock->name', $str);
    }

    /**
     * @param string $str
     */
    public function carrier(string $str)
    {
        $this->builder->where('carrier', $str);
    }

    /**
     * @param string $str
     */
    public function weight(string $str)
    {
        $this->builder->where('weight', $str);
    }

    /**
     * @param string $str
     */
    public function driver(string $str)
    {
        $this->builder->where('driver', $str);
    }

    /**
     * @param integer $num
     */
    public function route(int $num)
    {
        $this->builder->where('id', $num);
    }

    public function dateStart () {
        $this->builder->whereBetween(
            'created_at',
            [
                Carbon::parse($this->fields()['filter']['date_start']),
                Carbon::parse($this->fields()['filter']['date_end'])
            ]
        );
    }
}
