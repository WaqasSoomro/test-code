<?php
namespace App\Http\Resources\Api\ParentSharing\Players;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->position_id ?? $this->id,
            'name' => $this->name
        ];
    }
}