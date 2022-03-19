<?php

namespace App\Filters;

use Illuminate\Support\Carbon;

class ShipmentFilter extends QueryFilter
{

    /**
     * @param array $arr
     */
    public function filterCar(array $arr)
    {
        $this->builder->whereIn('car', $arr);
    }

    /**
     * @param array $arr
     */
    public function filterStockName($arr)
    {
        $this->builder->whereIn('stock->name', $arr);
    }

    /**
     * @param array $arr
     */
    public function filterCarrier(array $arr)
    {
        $this->builder->whereIn('carrier', $arr);
    }

    /**
     * @param array $arr
     */
    public function filterWeight(array $arr)
    {
        $this->builder->whereIn('weight', $arr);
    }

    /**
     * @param array $arr
     */
    public function filterDriver(array $arr)
    {
        $this->builder->whereIn('driver', $arr);
    }

    /**
     * @param array $arr
     */
    public function filterRoute(array $arr)
    {
        $this->builder->whereIn('id', $arr);
    }

    public function filterDateStart() {
        $this->builder->whereBetween(
            'created_at',
            [
                Carbon::parse($this->fields()['filter']['date_start']),
                Carbon::parse($this->fields()['filter']['date_end'])
            ]
        );
    }
}
