<?php
namespace App\Http\Requests\Api\Dashboard\Clubs\Teams\Players;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $validations = [
            'teamId' => 'required|numeric|exists:teams,id',
            'positionsId' => 'required|array'
        ];

        if (!empty($this->positionsId) && current($this->positionsId) != 'all')
        {
            $validations = [
                'positionsId.*' => 'exists:positions,id'
            ];
        }

        return $validations;
    }
}