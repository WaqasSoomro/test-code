<?php

namespace App\Http\Resources\Api\TrainerApp\Events;

use Illuminate\Http\Resources\Json\JsonResource;

class TrainerAppEventListingCategoriesResource extends JsonResource
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
            'title' => lcfirst($this->title)
        ];
    }
}
