<?php
namespace App\Http\Requests\Api\Dashboard\Clubs\Teams\Players;
use Illuminate\Foundation\Http\FormRequest;

class ChartRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            "teamId" => "required|numeric|exists:team_trainers,team_id,trainer_user_id,".auth()->user()->id,
            "playerId" => "required|numeric|exists:players,id"
        ];
    }
}