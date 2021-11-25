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
        $this->builder->whereRaw("LOWER('.$name.') LIKE '%'". strtolower($name)."'%'");
    }

    /**
     * @param string $host
     */
    public function host(string $host)
    {
        $this->builder->whereRaw("LOWER('.$host.') LIKE '%'". strtolower($host)."'%'");
    }
}
