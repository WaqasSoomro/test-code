<?php
namespace App\Http\Requests\Api\App\Events;
use Illuminate\Foundation\Http\FormRequest;

class AttendingEventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'eventId' => 'required|exists:event_players,event_id,player_id,'.auth()->user()->id,
            'isAttending' => 'required|in:yes,no'
        ];
    }
}
