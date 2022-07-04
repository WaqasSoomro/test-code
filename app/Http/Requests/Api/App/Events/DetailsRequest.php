<?php
namespace App\Http\Requests\Api\App\Events;
use Illuminate\Foundation\Http\FormRequest;

class DetailsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start' => 'required|date|date_format:Y-m-d H:i:s',
            'end' => 'required|date|date_format:Y-m-d H:i:s|after:start',
            'groupId' => 'required|exists:events,group_id'
        ];
    }
}