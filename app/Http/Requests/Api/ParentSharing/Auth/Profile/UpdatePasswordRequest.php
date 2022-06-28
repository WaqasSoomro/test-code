<?php
namespace App\Http\Requests\Api\ParentSharing\Auth\Profile;
use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return Helper::updatePasswordRequest();
    }
}