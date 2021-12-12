<?php

namespace App\Http\Requests\Api\RetailOutlets;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:outlets,code',
            'address' => 'required|string|max:255',
            'lng' => 'required|numeric',
            'lat' => 'required|numeric',
            'radius' => 'numeric',
        ];
    }
}
