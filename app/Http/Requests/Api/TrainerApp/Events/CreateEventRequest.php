<?php

namespace App\Http\Requests\Api\TrainerApp\Events;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
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
        $generalInputs = self::generalInputs();

        $conditionalInputs = self::conditionalInputs();

        $editInputs = self::editInputs();

        $validations = array_merge($generalInputs, $conditionalInputs, $editInputs);

        return $validations;
    }
    public function generalInputs()
    {
        $validations = [
            'clubId' => 'required|numeric|min:1|exists:clubs,id',
            'type' => 'required|in:training,assignment,match,event',
            'categoryId' => 'required|numeric|min:1|exists:event_categories,id,status,active',
            'title' => 'required|string|min:1|max:100',
            'start' => 'required|date|date_format:Y-m-d H:i:s|after_or_equal:now',
            'end' => 'required|date|date_format:Y-m-d H:i:s|after:start',
            'repetitionId' => 'required|numeric|exists:event_repetitions,id,status,active',
            'location' => 'required|string',
            'latitude' => 'required',
            'longitude' => 'required',
            'teamId' => 'required|numeric|exists:teams,id',
            'positionsId' => 'required|array',
            'positionsId.*' => 'numeric|exists:positions,id',
            'playersId' => 'required|array',
            'playersId.*' => 'numeric|exists:players,user_id',
            'details' => 'nullable'
        ];

        return $validations;
    }

    public function conditionalInputs()
    {
        if (preg_match('%training%i', $this->type))
        {
            $conditionalInputs = self::trainingInputs();
        }
        else if (preg_match('%assignment%i', $this->type))
        {
            $conditionalInputs = self::assignmentInputs();
        }
        else if (preg_match('%match%i', $this->type))
        {
            $conditionalInputs = self::matchInputs();
        }
        else
        {
            $conditionalInputs = [];
        }

        return $conditionalInputs;
    }

    public function trainingInputs()
    {
        $validations = [
            'eventTypeId' => 'required|numeric|exists:event_types,id,status,active'
        ];

        return $validations;
    }

    public function assignmentInputs()
    {
        $validations = [
            'assignmentId' => 'required|numeric|exists:assignments,id,trainer_user_id,'.auth()->user()->id
        ];

        return $validations;
    }

    public function matchInputs()
    {
        $validations = [
            'opponentTeamId' => 'required|numeric|exists:teams,id',
            'opponentPositionsId' => 'required|array',
            'opponentPositionsId.*' => 'numeric|exists:positions,id',
            'opponentPlayersId' => 'required|array',
            'opponentPlayersId.*' => 'numeric|exists:players,user_id',
            'playingAreaId' => 'required|numeric|exists:event_match_types,id,status,active'
        ];

        return $validations;
    }

    public function editInputs()
    {
        if (!empty($this->segment(6)) && is_numeric($this->segment(6)))
        {
            $validations = [
                'groupId' => 'required|exists:events,group_id',
                'actionType' => 'required|in:single,current_&_upcoming',
                'currentStartDate' => 'required|date|date_format:Y-m-d H:i:s',
                'currentEndDate' => 'required|date|date_format:Y-m-d H:i:s|after:currentStartDate'
            ];
        }
        else
        {
            $validations = [];
        }

        return $validations;
    }
}
