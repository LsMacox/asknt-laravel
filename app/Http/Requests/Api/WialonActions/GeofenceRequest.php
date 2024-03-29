<?php

namespace App\Http\Requests\Api\WialonActions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeofenceRequest extends FormRequest
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
            'unit_id' => 'required|exists:wialon_notifications,id',
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
