<?php

namespace App\Http\Resources\Api\TrainerApp\Events;

use App\Http\Resources\Api\Dashboard\Teams\IndexResource as TeamsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerAppByDateResource extends JsonResource
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
            'category' => new TrainerAppEventListingCategoriesResource($this->category),
            'title' => $this->title,
            'start' => $this->from_date_time,
            'end' => $this->to_date_time,
            'repetition' => $this->event_repetition,
            'isAttending' => $this->players[0]->pivot->is_attending,
            'team' => new TeamsResource($this->team),
        ];
    }
}
