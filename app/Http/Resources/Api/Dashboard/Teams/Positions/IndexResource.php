<?php
namespace App\Http\Resources\Api\Dashboard\Teams\Positions;
use \App\Http\Resources\Api\Dashboard\Teams\Positions\LinesResource;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'line' => (new LinesResource($this->line))->resolve(),
        ];
    }
}