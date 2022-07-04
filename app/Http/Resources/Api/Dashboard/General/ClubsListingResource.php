<?php
namespace App\Http\Resources\Api\Dashboard\General;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubsListingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->title,
            'image' => $this->image ?? "",
            'primaryColor' => $this->primary_color,
            'secondaryColor' => $this->secondary_color,
            'isOwner' => $this->owner_id == auth()->user()->id ? true : false
        ];
    }
}