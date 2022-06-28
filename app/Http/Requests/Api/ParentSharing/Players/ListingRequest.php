<?php
namespace App\Http\Requests\Api\ParentSharing\Players;
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
            'limit' => 'required|numeric',
            'offset' => 'required|numeric'
        ];
    }
}