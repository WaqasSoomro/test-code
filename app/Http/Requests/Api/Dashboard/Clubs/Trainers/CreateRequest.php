<?php
namespace App\Http\Requests\Api\Dashboard\Clubs\Trainers;
use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clubId' => 'required|numeric|min:1|exists:clubs,id,owner_id,'.auth()->user()->id,
            'firstNames' => 'required|array',
            'firstNames.*' => 'string|min:3|max:25',
            'lastNames' => 'required|array',
            'lastNames.*' => 'string|min:3|max:25',
            'emails' => 'required|array',
            'emails.*' => 'email|min:8|max:254|unique:users,email,NULL,id,deleted_at,NULL',
            'countryCodes' => 'required|array',
            'countryCodes.*' => 'numeric|digits_between:1,4|exists:countries,phone_code',
            'phoneNos' => 'required|array',
            'phoneNos.*' => 'numeric|digits_between:4,12',
            'assignedTeams' => 'required|array',
            'assignedTeams.*.*' => 'numeric|exists:teams,id'
        ];
    }

    public function messages()
    {
        return [
            'firstNames.*.string' => 'The first name :input value must be a string.',
            'firstNames.*.min' => 'The first name :input must be at least :min characters.',
            'firstNames.*.max' => 'The first name :input may not be greater than :max characters.',
            'lastNames.*.string' => 'The last name :input value must be a string.',
            'lastNames.*.min' => 'The last name :input must be at least :min characters.',
            'lastNames.*.max' => 'The last name :input may not be greater than :max characters.',
            'emails.*.email' => 'The email :input must be a valid email address.',
            'emails.*.min' => 'The email :input must be at least :min characters.',
            'emails.*.max' => 'The email :input may not be greater than :max characters.',
            'emails.*.unique' => 'The email :input has already been taken.',
            'countryCodes.*.numeric' => 'The country code :input must be a number.',
            'countryCodes.*.digits_between' => 'The country code :input must be between :min and :max digits.',
            'countryCodes.*.exists' => 'The selected country code :input is invalid.',
            'phoneNos.*.numeric' => 'The phone no :input must be a number.',
            'phoneNos.*.digits_between' => 'The phone no :input must be between :min and :max digits.',
            'assignedTeams.*.*.numeric' => 'The selected team id :input must be a number.',
            'assignedTeams.*.*.exists' => 'The selected team id :input is invalid.'
        ];
    }
}