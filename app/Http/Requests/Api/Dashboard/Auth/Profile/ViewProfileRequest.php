<?php
namespace App\Http\Requests\Api\Dashboard\Auth\Profile;
use Illuminate\Foundation\Http\FormRequest;

class ViewProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'token' => 'required|string|exists:users,remember_token',
        ];
    }
}