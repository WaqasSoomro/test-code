<?php
namespace App\Http\Resources\Api\App\Events;
use App\Http\Resources\Api\App\Events\CategoriesResource;
use App\Http\Resources\Api\Dashboard\Teams\IndexResource as TeamsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ByDateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'groupId' => $this->group_id,
            'category' => new CategoriesResource($this->category),
            'title' => $this->title,
            'start' => $this->from_date_time,
            'end' => $this->to_date_time,
            'repetition' => $this->event_repetition,
            'isAttending' => $this->players[0]->pivot->is_attending,
            'team' => new TeamsResource($this->team),
        ];
    }
}