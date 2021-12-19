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
            'id_sap' => 'required_without:id_1c|max:255',
            'id_1c' => 'required_without:id_sap|max:255',
            'lng' => 'required|numeric',
            'lat' => 'required|numeric',
            'radius' => 'numeric|max:9999',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Название',
            'id_sap' => 'id SAP',
            'id_1c' => 'id 1C',
            'lng' => 'Долгота центра',
            'lat' => 'Широта центра',
            'radius' => 'Радиус зоны',
        ];
    }
}
