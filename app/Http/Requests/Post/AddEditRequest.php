<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

/**
 *
 * @bodyParam post_title string required allowed max 191 chars
 * @bodyParam post_desc longtext required
 * @bodyParam post_attachment file allowed mime_types: jpeg, png, avi, mp4, mkv
 */
class AddEditRequest extends FormRequest
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
            'post_title' => 'required|max:191',
            'post_desc' => 'required',
            'post_attachment' => 'nullable'
        ];
    }
}
