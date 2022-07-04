<?php

namespace App\Http\Resources\Api\TrainerApp\Events;

use Illuminate\Http\Resources\Json\JsonResource;

class TrainerAppCategoriesResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => lcfirst($this->title),
            'engTitle' => lcfirst($this->engTitle),
            'color' => $this->color,
            'status' => $this->status
        ];
    }
}
