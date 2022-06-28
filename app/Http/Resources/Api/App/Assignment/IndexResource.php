<?php
namespace App\Http\Resources\Api\App\Assignment;
use App\Http\Resources\Api\App\Exercises\IndexResource as ExercisesResource;
use App\Http\Resources\Api\App\Skills\IndexResource as SkillsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $this->image ?? "",
            'exercises' => ExercisesResource::collection($this->exercises)->toArray($request),
            'skills' => SkillsResource::collection($this->skills)->toArray($request)
        ];
    }
}