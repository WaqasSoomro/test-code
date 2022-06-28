<?php

namespace App\Http\Requests\Api\App\PlayersSensorsSessions;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            "limit" => "required|numeric",
            "offset" => "required|numeric",
            "player_id" => "nullable|numeric"
        ];
    }
}