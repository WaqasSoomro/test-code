<?php
namespace App\Http\Requests\Api\Dashboard\Auth;
use Illuminate\Foundation\Http\FormRequest;

class ResendSetupPasswordLinkRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'token' => 'required|string|exists:users,remember_token',
            'otp' => 'required|numeric|digits:6|exists:users,verification_code,remember_token,'.$this->token
        ];
    }
}