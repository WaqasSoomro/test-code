<?php
namespace App\Http\Requests\Api\Dashboard\Clubs\Trainers;
use Illuminate\Foundation\Http\FormRequest;

class ApproveJoiningRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clubId' => 'required|numeric|min:1|exists:clubs,id',
            'trainerId' => 'required|numeric|min:1|exists:club_trainers,trainer_user_id',
            'action' => 'required|string|in:yes,no'
        ];
    }
}