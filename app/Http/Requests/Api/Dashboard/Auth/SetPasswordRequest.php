<?php
namespace App\Http\Requests\Api\Dashboard\Auth;
use Illuminate\Foundation\Http\FormRequest;

class SetPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|min:8|max:254|exists:users,email,deleted_at,NULL,remember_token,'.$this->token,
            'newPassword' => 'required|string|min:8|max:55',
            'confirmPassword' => 'required|string|min:8|max:55|same:newPassword',
            'token' => 'required|string|exists:users,remember_token,email,'.$this->email,
            'otp' => 'required|numeric|digits:6|exists:users,verification_code,remember_token,'.$this->token,
            'deviceType' => 'required|in:web',
            'deviceToken' => 'nullable',
            'ip' => 'required',
            'macId' => 'required'
        ];
    }
}