<?php
namespace App\Http\Requests\Api\Dashboard\ParentSharing;
use Illuminate\Foundation\Http\FormRequest;

class ListingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'playerId' => 'required|numeric|exists:players,user_id|exists:users,id'
        ];
    }
}