<?php
namespace App\Http\Resources\Api\ParentSharing\Teams;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayersTeamsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->team_id,
            'name' => $this->team_name
        ];
    }
}