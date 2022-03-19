<?php

namespace App\Http\Requests\Api\ShipmentList;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipmentFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'offset' => 'integer',
            'limit' => 'required_with:offset|integer',
            'sortBy' => 'required_with:offset|string',
            'sortByDesc' => 'boolean',

            'filter.car' => 'array',
            'filter.carrier' => 'array',
            'filter.date_start' => 'date',
            'filter.date_end' => 'date',
            'filter.driver' => 'array',
            'filter.weight' => 'array',
            'filter.stock_name' => 'array',
            'filter.route' => 'array',

            'filter.car.*' => 'string|exists:shipments,car',
            'filter.carrier.*' => 'string|exists:shipments,carrier',
            'filter.driver.*' => 'string|exists:shipments,driver',
            'filter.weight.*' => 'string|exists:shipments,weight',
            'filter.stock_name.*' => 'string',
            'filter.route.*' => 'numeric|exists:shipments,id',
        ];
    }

    public function attributes()
    {
        return [
            'filter.car' => 'Машина',
            'filter.carrier' => 'Перевозчик',
            'filter.date_start' => 'Дата отгрузки (начало)',
            'filter.date_end' => 'Дата отгрузки (конец)',
            'filter.driver' => 'Широта центра',
            'filter.weight' => 'Грузоподъемность',
            'filter.stock_name' => 'Склад отгрузки',
            'filter.route' => 'Маршрут',
        ];
    }
}
