<?php

namespace App\Http\Requests\Api\LoadingZone;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'lng' => 'required|numeric|between:-180,180',
            'lat' => 'required|numeric|between:-90,90',
            'radius' => 'numeric|max:9999',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Название',
            'lng' => 'Долгота центра',
            'lat' => 'Широта центра',
            'radius' => 'Радиус зоны',
        ];
    }
}
