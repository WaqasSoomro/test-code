<?php
namespace App\Http\Requests\Api\ParentSharing\Auth;
use Illuminate\Foundation\Http\FormRequest;

class SignUpRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'firstName' => 'required|string|min:3|max:25',
            'lastName' => 'required|string|min:3|max:25',
            'email' => 'required|email|min:8|max:254|unique:users,email,NULL,id,deleted_at,NULL',
            'newPassword' => 'required|string|min:8|max:55',
            'confirmPassword' => 'required|string|min:8|max:55|same:newPassword',
            'deviceType' => 'required|in:web',
            'deviceToken' => 'nullable',
            'ip' => 'required',
            'macId' => 'required'
        ];
    }
}