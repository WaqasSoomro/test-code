<?php
namespace App\Http\Requests\Api\Dashboard\Events;
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
            'months' => 'required|array',
            'months.*' => 'numeric|digits:2|between:1,12',
            'years' => 'required|array',
            'years.*' => 'numeric|digits:4|min:2021|max:'.date('Y', strtotime('+10 Years')).'',
            'limit' => 'required|numeric',
            'offset' => 'required|numeric'
        ];
    }
}