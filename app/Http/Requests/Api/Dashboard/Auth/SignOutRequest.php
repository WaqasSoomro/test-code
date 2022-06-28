<?php
namespace App\Http\Requests\Api\Dashboard\Auth;
use Illuminate\Foundation\Http\FormRequest;

class SignOutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'deviceType' => 'required|in:web|exists:user_devices,device_type,user_id,'.auth()->user()->id,
            'deviceToken' => 'nullable|exists:user_devices,device_token,user_id,'.auth()->user()->id,
            'ip' => 'required|exists:user_devices,ip,user_id,'.auth()->user()->id,
            'macId' => 'required|exists:user_devices,mac_id,user_id,'.auth()->user()->id
        ];
    }
}