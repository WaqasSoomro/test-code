<?php
namespace App\Http\Resources\Api\Dashboard\ParentSharing;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'email' => $this->parent_email
        ];
    }
}