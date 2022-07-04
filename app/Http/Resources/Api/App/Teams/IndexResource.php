<?php
namespace App\Http\Resources\Api\App\Teams;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->team_name,
            'image' => $this->image ?? ''
        ];
    }
}