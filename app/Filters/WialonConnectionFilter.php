<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class WialonConnectionFilter extends QueryFilter
{
    /**
     * @param string $name
     */
    public function name(string $name)
    {
        $this->builder->where("LOWER('.$name.') LIKE '%'". strtolower($name)."'%'");
    }
}
