<?php

namespace App\Http\Resources\Api\App;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GetTeamPlayerResourceListing extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (is_array($request->followings_ids)) {
            return [
                "id" => $this->pivot->user_id,
                "first_name" => $this->first_name ?? "",
                "middle_name" => $this->middle_name ?? "",
                "last_name" => $this->last_name ?? "",
                "profile_picture" => $this->profile_picture ?? "",
                "follow_status" => Auth::user()->id != $this->pivot->user_id
                    ?
                    in_array($this->pivot->user_id, $request->followings_ids) ? true : false
                    : null
            ];
        }
        else{
            return [
                "id" => $this->pivot->user_id,
                "first_name" => $this->first_name ?? "",
                "middle_name" => $this->middle_name ?? "",
                "last_name" => $this->last_name ?? "",
                "profile_picture" => $this->profile_picture ?? "",
            ];
        }
    }
}
