<?php
namespace App\Http\Resources\Api\App\Exercises;
use App\Http\Resources\Api\App\Tools\IndexResource as ToolsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->title,
            'image' => $this->image ?? "",
            'tools' => ToolsResource::collection($this->tools)->toArray($request)
        ];
    }
}