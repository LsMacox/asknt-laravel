<?php


namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param Builder $builder
     */
    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->fields() as $field => $value) {
            $method = camel_case($field);
            if (method_exists($this, $method)) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        call_user_func_array([$this, $method], (array)$v);
                    }
                } else {
                    call_user_func_array([$this, $method], (array)$value);
                }
            }
        }
    }

    /**
     * @return array
     */
    protected function fields(): array
    {
        return array_filter(array_map(
            function ($v) {
                return $this->recursiveTrim($v);
            },
            $this->request->all()
        ));
    }

    protected function recursiveTrim($v) {
        if (is_array($v)) {
            return $this->recursiveTrim($v);
        }
        return trim($v);
    }

}
