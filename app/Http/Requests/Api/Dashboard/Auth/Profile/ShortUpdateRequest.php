<?php
namespace App\Http\Requests\Api\Dashboard\Auth\Profile;
use Illuminate\Foundation\Http\FormRequest;

class ShortUpdateRequest extends FormRequest
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
            'countryCode' => 'required|numeric|digits_between:1,4|exists:countries,phone_code',
            'phoneNo' => 'required|numeric|digits_between:4,12',
            'image' => 'nullable|file|mimes:jpeg,jpg,png'
        ];
    }
}