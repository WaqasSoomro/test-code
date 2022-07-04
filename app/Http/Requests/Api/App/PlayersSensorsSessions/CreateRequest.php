<?php
namespace App\Http\Requests\Api\App\PlayersSensorsSessions;
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
            "right_foot_file" => "required|file|mimes:json",
            "left_foot_file" => "required|file|mimes:json",
            "Logging_info" => "nullable|json"
        ];
    }
}