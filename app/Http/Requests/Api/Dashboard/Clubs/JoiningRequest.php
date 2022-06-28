<?php
namespace App\Http\Requests\Api\Dashboard\Clubs;
use Illuminate\Foundation\Http\FormRequest;

class JoiningRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|numeric|min:1|exists:clubs,id'
        ];
    }
}