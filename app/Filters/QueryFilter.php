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
            if (is_array($value)) {
                foreach ($value as $f => $v) {
                    $this->execFilters($f, $v);
                }
            } else {
                $this->execFilters($field, $value);
            }
        }
    }

    /**
     * @param $method
     * @param $value
     */
    protected function execFilters ($method, $value) {
        $method = camel_case($method);
        if (method_exists($this, $method)) {
            call_user_func_array([$this, $method], (array)$value);
        }
    }

    /**
     * @return array
     */
    protected function fields(): array
    {
        return array_filter(array_map(
            function ($v) {
                return is_string($v) ? trim($v) : $v;
            },
            $this->request->all()
        ));
    }

}
