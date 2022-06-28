<?php

namespace App\Http\Resources\Api\TrainerApp;

use Illuminate\Http\Resources\Json\JsonResource;

class TrainerAppPlayersListingResource extends JsonResource
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

        return [
            "id"=> $this->id,
            "player_name"=> $this->first_name . ' ' . $this->last_name,
            "profile_picture"=> $this->profile_picture,
            "age"=> $this->age,
            "gender"=> $this->gender,
            "positions" => $positions,
            "teams"=>$this->teams ?? []
        ];
    }
}
