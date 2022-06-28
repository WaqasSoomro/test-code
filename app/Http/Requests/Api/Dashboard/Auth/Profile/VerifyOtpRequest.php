<?php
namespace App\Http\Requests\Api\Dashboard\Auth\Profile;
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
            'otp' => 'required|numeric|digits:6|exists:users,verification_code,id,'.auth()->user()->id,
        ];
    }
}