<?php
namespace App\Http\Resources\Api\Dashboard\Clubs;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'userName' => $this->user_name,
            'name' => $this->title,
            'type' => $this->type,
            'primaryColor' => $this->primary_color,
            'secondaryColor' => $this->secondary_color,
            'privacy' => ucwords(str_replace('_', ' ', $this->privacy)),
            'image' => $this->image,
            'is_verified' => ucfirst($this->is_verified)
        ];
    }
}