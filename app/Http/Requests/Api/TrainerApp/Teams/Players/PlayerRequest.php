<?php

namespace App\Http\Requests\Api\TrainerApp\Teams\Players;

use Illuminate\Foundation\Http\FormRequest;

class PlayerRequest extends FormRequest
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
        $validations = [
            'teamId' => 'required|numeric|exists:teams,id',
            'positionsId' => 'required|array'
        ];
        if (!is_array($this->positionsId)){
            return $validations;
        }
        if (!empty($this->positionsId) && current($this->positionsId) != 'all')
        {
            $validations = [
                'positionsId.*' => 'exists:positions,id'
            ];
        }

        return $validations;
    }
}
