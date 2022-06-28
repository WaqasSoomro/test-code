<?php
namespace App\Http\Requests\Api\Dashboard\Events;
use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'actionType' => 'required|in:single,bulk',
            'start' => 'required|date|date_format:Y-m-d H:i:s',
            'end' => 'required|date|date_format:Y-m-d H:i:s|after:start',
            'groupId' => 'required|exists:events,group_id',
            'clubId' => 'required|numeric|min:1|exists:clubs,id'
        ];
    }
}