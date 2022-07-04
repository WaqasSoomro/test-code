<?php
namespace App\Http\Resources\Api\Dashboard\Teams\Players;
use \App\Http\Resources\Api\Dashboard\Teams\Positions\IndexResource as PositionsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->first_name.' '.$this->last_name,
            'image' => $this->profile_picture ?? '',
            'positions' => isset($this->player) && count($this->player->positions) > 0 ? PositionsResource::collection($this->player->positions)->toArray($request) : [],
            'team_name' => $request->team->team_name ?? "",
            'isAttending' => $this->pivot->is_attending ?? 'no'
        ];
    }
}