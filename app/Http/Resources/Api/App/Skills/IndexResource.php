<?php
namespace App\Http\Resources\Api\App\Skills;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image ?? ""
        ];
    }
}