<?php
namespace App\Http\Resources\Api\Dashboard\Clubs\Teams;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->team_name
        ];
    }
}