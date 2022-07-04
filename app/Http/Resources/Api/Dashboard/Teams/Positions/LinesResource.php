<?php
namespace App\Http\Resources\Api\Dashboard\Teams\Positions;
use Illuminate\Http\Resources\Json\JsonResource;

class LinesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}