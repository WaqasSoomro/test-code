<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
* @queryParam  first_name  string required . First name of the user
* @queryParam  middle_name  string required . Middle name of the user
* @queryParam  last_name string  required . Last name of the user
* @queryParam  email required . Email of the user
* @queryParam  phone required . Phone Number of the user
* @queryParam  select_user required . Either user is trainer (1) or player (2) , 1 int
                represent trainer & 2 int represents player.
* @queryParam  password string  required . Password of the user
* @queryParam  confirm_password string  required . Confirm password to match with password
* @queryParam  country string  required . if user wants to be trainer then this field is
                required for trainer(user)
* @queryParam  height float  required . if user wants to be player then this field is
                required for player(user)
* @queryParam  weight float  required . if user wants to be player then this field is
                required for player(user)
*/

class RegisterRequest extends FormRequest
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
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|regex:/(01)[0-9]{9}/',
            'gender' => 'required',
            'select_user' => 'required|min:1|max:2',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            //'status_id' => 'nullable|min:0|max:1',

        ];



    }
}
