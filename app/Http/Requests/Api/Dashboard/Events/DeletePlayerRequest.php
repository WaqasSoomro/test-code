<?php
namespace App\Http\Requests\Api\Dashboard\Events;
use Illuminate\Foundation\Http\FormRequest;

class DeletePlayerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clubId' => 'required|numeric|min:1|exists:clubs,id',
            'eventId' => 'required|numeric|min:1|exists:events,id'
        ];
    }
}