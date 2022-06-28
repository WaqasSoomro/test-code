<?php
namespace App\Http\Resources\Api\Dashboard\Events;
use App\Http\Resources\Api\Dashboard\Events\CategoriesResource;
use App\Http\Resources\Api\Dashboard\Teams\IndexResource as TeamsResource;
use App\Http\Resources\Api\Dashboard\Teams\Players\DetailsResource as PlayersResource;
use App\Http\Resources\Api\Dashboard\Assignment\IndexResource as AssignmentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray($request)
    {
        $generalColumns = [
            'id' => $this->id,
            'category' => new CategoriesResource($this->category),
            'groupId' => $this->group_id,
            'title' => $this->title,
            'start' => $this->from_date_time,
            'end' => $this->to_date_time,
            'repetition' => $this->event_repetition,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'team' => new TeamsResource($this->team),
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