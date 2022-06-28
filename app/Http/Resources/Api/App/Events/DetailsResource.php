<?php
namespace App\Http\Resources\Api\App\Events;
use App\Http\Resources\Api\App\Events\CategoriesResource;
use App\Http\Resources\Api\App\Teams\IndexResource as TeamsResource;
use App\Http\Resources\Api\Dashboard\Teams\Players\IndexResource as PlayersResource;
use App\Http\Resources\Api\App\Assignment\IndexResource as AssignmentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailsResource extends JsonResource
{
    public function toArray($request)
    {
        $request->team = $this->team;
        
        $generalColumns = [
            'id' => $this->id,
            'category' => new CategoriesResource($this->category),
            'title' => $this->title,
            'start' => $this->from_date_time,
            'end' => $this->to_date_time,
            'repetition' => $this->event_repetition,
            'timeSt' => date('H:i'),
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'team' => new TeamsResource($this->team),
            'trainer' => new PlayersResource($this->added_by),
            'players' => PlayersResource::collection($this->players()->withPivot('is_attending')->where('team_type', 'my_team')->get())->toArray($request),
            'details' => $this->details ?? ""
        ];

        if (preg_match('%training%i', $this->category->engTitle))
        {
            $conditionalColumns = [
                'eventType' => $this->event_created_type
            ];
        }
        else if (preg_match('%assignment%i', $this->category->engTitle))
        {
            $conditionalColumns = [
                'assignment' => new AssignmentResource($this->assignment)
            ];
        }
        else if (preg_match('%match%i', $this->category->engTitle))
        {
            $conditionalColumns = [
                'opponentTeam' => new TeamsResource($this->opponent_team),
                'opponentTeamPlayers' => PlayersResource::collection($this->players()->where('team_type', 'opponent_team')->get())->toArray($request),
                'playingArea' => $this->event_playing_area
            ];
        }
        else
        {
            $conditionalColumns = [];
        }

        $record = array_merge($generalColumns, $conditionalColumns);

        return $record;
    }
}