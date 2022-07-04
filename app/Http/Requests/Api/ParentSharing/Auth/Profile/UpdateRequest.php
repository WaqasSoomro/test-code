<?php
namespace App\Http\Requests\Api\ParentSharing\Auth\Profile;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|min:8|max:254|unique:users,email,'.auth()->user()->id.',id,deleted_at,NULL',
            'firstName' => 'required|string|min:3|max:25',
            'lastName' => 'required|string|min:3|max:25'
        ];
    }
}