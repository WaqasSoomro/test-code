<?php

namespace App\Http\Resources\Api\V3Dashboard;

use App\Http\Resources\Api\ParentSharing\Players\PositionsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FilteredPlayerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $positions = [];

        foreach ($this->player->positions as $position)
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

        return [
            "id"=>$this->id,
            "first_name"=>$this->first_name,
            "last_name"=>$this->last_name,
            "profile_picture"=>$this->profile_picture,
            "positions"=>$positions,
            "team_name"=>count($this->teams) > 0 ? $this->teams[0]->team_name : "",
            "total_exercises"=>$this->total_exercises,
            "completed_exercises"=>$this->completed_exercises,
            "total_comments"=>$this->total_comments
        ];
    }
}
