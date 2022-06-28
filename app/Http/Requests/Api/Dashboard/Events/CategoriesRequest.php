<?php
namespace App\Http\Requests\Api\Dashboard\Events;
use Illuminate\Foundation\Http\FormRequest;

class CategoriesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'limit' => 'required|numeric',
            'offset' => 'required|numeric'
        ];
    }
}