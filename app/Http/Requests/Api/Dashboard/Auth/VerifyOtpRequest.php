<?php
namespace App\Http\Requests\Api\Dashboard\Auth;
use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|min:8|max:254|exists:users,email,deleted_at,NULL',
            'password' => 'required|string|min:8|max:55',
            'otp' => 'required|numeric|digits:6',
            'deviceType' => 'required|in:web',
            'deviceToken' => 'nullable',
            'ip' => 'required',
            'macId' => 'required'
        ];
    }
}