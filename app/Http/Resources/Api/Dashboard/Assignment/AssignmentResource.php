<?php

namespace App\Http\Resources\Api\Dashboard\Assignment;

use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
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
          "id"=>$this->id,
          "trainer_user_id"=>$this->trainer_user_id,
            "difficulty_level"=>$this->difficulty_level,
            "title"=>$this->title,
            "assign_to"=>$this->assign_to,
            "description"=>$this->description,
            "image"=>$this->image,
            "deadline"=>$this->deadline,
            "created_at"=>$this->created_at,
            "updated_at"=>$this->updated_at,
            "deleted_at"=>$this->deleted_at,
            "exercises_count"=>$this->exercises_count,
            "players_count"=>$this->players_count,
            "player_completed_count"=>$this->player_completed_count
        ];
    }
}
