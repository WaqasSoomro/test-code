<?php
namespace App\Http\Requests\Api\ParentSharing\Auth;
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
            'otp' => 'required|numeric|digits:6',
            'deviceType' => 'required|in:web',
            'deviceToken' => 'nullable',
            'ip' => 'required',
            'macId' => 'required'
        ];
    }
}