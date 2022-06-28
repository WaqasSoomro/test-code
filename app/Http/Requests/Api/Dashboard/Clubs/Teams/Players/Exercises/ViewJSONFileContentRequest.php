<?php
namespace App\Http\Requests\Api\Dashboard\Clubs\Teams\Players\Exercises;
use Illuminate\Foundation\Http\FormRequest;

class ViewJSONFileContentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'playerId' => 'required|numeric|exists:users,id,status_id,1|exists:players,user_id',
            'exerciseId' => 'required|numeric|exists:exercises,id,is_active,1',
            'playerExerciseId' => 'required|numeric|exists:player_exercise,id,user_id,'.$this->playerId.',exercise_id,'.$this->exerciseId
        ];
    }
}