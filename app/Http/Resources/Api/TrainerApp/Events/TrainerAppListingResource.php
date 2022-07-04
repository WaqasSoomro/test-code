<?php

namespace App\Http\Resources\Api\TrainerApp\Events;

use App\Http\Resources\Api\Dashboard\Assignment\IndexResource as AssignmentResource;
use App\Http\Resources\Api\Dashboard\Teams\IndexResource as TeamsResource;
use App\Http\Resources\Api\Dashboard\Teams\Players\IndexResource as PlayerResource;
use App\Http\Resources\Api\Dashboard\Teams\Players\DetailsResource as PlayersResource;
use App\Http\Resources\Api\Dashboard\Teams\Positions\IndexResource as PositionsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerAppListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $generalColumns = [
            'id' => $this->id,
            'category' => new TrainerAppCategoriesResources($this->category),
            'groupId' => $this->group_id ?? 0,
            'title' => $this->title,
            'start' => $this->from_date_time,
            'end' => $this->to_date_time,
            'repetition' => $this->event_repetition,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'team' => new PlayerResource($this->team),
            'trainer' => new IndexResource($this->added_by),
            'players' => PlayersResource::collection($this->players()->where('team_type', 'my_team')->get())->toArray($request),
            'details' => $this->details ?? ""
        ];


        // CREATE A SEPARATE KEY FOR PLAYER POSITIONS
        $positions = [];

        foreach ($this->player->positions as $positionIndex => $position)
        {
            $positions[] = [
                'id' => $position->id,
                'name' => $position->name,
                'line' => [
                    'id' => $position->line->id,
                    'name' => $position->line->name
                ]
            ];
        }

        $generalColumns["positions"] = $positions;

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

        // CREATE A SEPARATE KEY FOR OPPONENT TEAM PLAYERS POSITIONS
        $opponentTeamPositions = [];
        if (isset($conditionalColumns["opponentTeamPlayers"])) {
            if (count($conditionalColumns["opponentTeamPlayers"]) > 0) {
                foreach ($conditionalColumns["opponentTeamPlayers"] as $val) {
                    if (count($val["position"]) > 0) {
                        $opponentTeamPositions[] = $val["position"][0];
                    }
                }
                $conditionalColumns += ["opponentTeamPositions" => $opponentTeamPositions];
            }
        }

        $record = array_merge($generalColumns, $conditionalColumns);

        return $record;
    }
}
