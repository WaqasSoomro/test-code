<?php

namespace App\Http\Requests\Api\App;

use Illuminate\Foundation\Http\FormRequest;

class PlayerStatisticsRequest extends FormRequest
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
            'player_id' => 'required|exists:users,id',
            'from' => 'date|date_format:Y-m-d',
            'to' => 'date|date_format:Y-m-d'
        ];
    }
}
