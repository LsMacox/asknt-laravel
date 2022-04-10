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
            if ($field === 'filter') {
                foreach ($value as $f => $v) {
                    $method = 'filter'.ucfirst(camel_case($f));
                    $this->callMethod($method, $v);
                }
            } else {
                $method = camel_case($field);
                $this->callMethod($method, $value);
            }
        }
    }

    /**
     * @param $method
     * @param $value
     */
    protected function callMethod ($method, $value) {
        if (method_exists($this, $method)) {
            call_user_func_array([$this, $method], [$value]);
        }
    }

    /**
     * @return array
     */
    protected function fields(): array
    {
        return array_filter(array_map(
            function ($v) {
                if (is_string($v) && !empty(json_decode($v))) {
                    return (array) json_decode($v);
                }
                return is_string($v) ? trim($v) : $v;
            },
            $this->request->all()
        ));
    }

}
