<?php
namespace App\Http\Requests\Api\Dashboard\Clubs;
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
            'userName' => 'required|min:1|max:50',
            'name' => 'required|min:1|max:100',
            'type' => 'required|in:Amateur Club,Football Academy,Pro Club',
            'primaryColor' => 'required|string|min:3|max:15',
            'secondaryColor' => 'required|string|min:3|max:15',
            'privacy' => 'required|in:open_to_invites,closed_for_invites',
            'image' => 'nullable|file|mimes:jpeg,jpg,png'
        ];
    }
}