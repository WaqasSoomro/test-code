<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;


/**
* @queryParam  email required . Email is required for login
* @queryParam  password required . Password is required for login
*/

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'email' => 'required|exists:users,email',
            'password' => 'required',
            //'status_id' => 'nullable|min:0|max:1',
        ];

    }
}
