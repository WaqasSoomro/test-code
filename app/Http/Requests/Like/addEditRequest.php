<?php

namespace App\Http\Requests\Like;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam post_id required The id of the post
 */
class addEditRequest extends FormRequest
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
            'post_id' => 'required|exists:posts,id'
        ];
    }
}
