<?php
namespace App\Http\Requests\Api\Dashboard\Auth;
use Illuminate\Foundation\Http\FormRequest;

class AutoSignInRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|min:8|max:254|exists:users,email,deleted_at,NULL',
            'clubId' => 'nullable|numeric|min:1|exists:clubs,id',
            'deviceType' => 'required|in:web|exists:user_devices,device_type',
            'deviceToken' => 'nullable|exists:user_devices,device_token',
            'ip' => 'required|exists:user_devices,ip',
            'macId' => 'required|exists:user_devices,mac_id'
        ];
    }
}