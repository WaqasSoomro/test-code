<?php
namespace App\Http\Requests\Api\Dashboard\Notifications;
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
            'types' => 'nullable|array',
            'type.*' => 'string|exists:user_notifications,model_type',
            'limit' => 'required|numeric',
            'offset' => 'required|numeric'
        ];
    }
}