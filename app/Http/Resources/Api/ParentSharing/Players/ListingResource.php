<?php
namespace App\Http\Resources\Api\ParentSharing\Players;
use App\Http\Resources\Api\ParentSharing\Players\PositionsResource;
use App\Http\Resources\Api\ParentSharing\Teams\PlayersTeamsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'image' => $this->profile_picture ?? '',
            'positions' => PositionsResource::collection($this->player->positions)->toArray($request),
            'teams' => PlayersTeamsResource::collection($this->teams)->toArray($request)
        ];
    }
}