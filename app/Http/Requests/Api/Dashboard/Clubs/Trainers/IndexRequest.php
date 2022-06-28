<?php
namespace App\Http\Requests\Api\Dashboard\Clubs\Trainers;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clubId' => 'required|numeric|min:1|exists:clubs,id',
            'limit' => 'required|numeric',
            'offset' => 'required|numeric'
        ];
    }
}