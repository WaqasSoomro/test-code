<?php
namespace App\Http\Resources\Api\App\Tools;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->tool_name,
            'image' => $this->icon
        ];
    }
}
