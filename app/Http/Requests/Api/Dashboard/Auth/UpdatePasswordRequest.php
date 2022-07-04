<?php
namespace App\Http\Requests\Api\Dashboard\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|min:8|max:254|exists:users,email,deleted_at,NULL',
            'newPassword' => 'required|string|min:8|max:55',
            'confirmPassword' => 'required|string|min:8|max:55|same:newPassword',
            'otp' => 'required|numeric|digits:6|exists:users,verification_code,email,'.$this->email,
            'deviceType' => 'required|in:web',
            'deviceToken' => 'nullable',
            'ip' => 'required',
            'macId' => 'required'
        ];
    }
}