<?php
namespace App\Http\Requests\Api\Dashboard\Auth\Profile;
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
            'firstName' => 'required|string|min:1|max:25',
            'lastName' => 'required|string|min:1|max:25',
            'email' => 'required|email|min:8|max:254|unique:users,email,'.auth()->user()->id.',id,deleted_at,NULL',
            'countryCode' => 'required|numeric|digits_between:1,4|exists:countries,phone_code',
            'phoneNo' => 'required|numeric|digits_between:4,12',
            'nationalityId' => 'required|numeric|exists:countries,id',
            'languageId' => 'required|numeric|exists:languages,id',
            'image' => 'nullable|file|mimes:jpeg,jpg,png'
        ];
    }
}