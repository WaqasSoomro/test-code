<?php
namespace App\Http\Resources\Api\Dashboard\Teams\Players;
use \App\Http\Resources\Api\Dashboard\Teams\Positions\IndexResource as PositionsResource;
use \App\Http\Resources\Api\Dashboard\Teams\Positions\LinesResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->first_name.' '.$this->last_name,
            'image' => $this->profile_picture ?? '',
            'positions' => PositionsResource::collection($this->player->positions)->toArray($request),
            'isAttending' => $this->pivot->is_attending ?? 'no'
        ];
    }
}