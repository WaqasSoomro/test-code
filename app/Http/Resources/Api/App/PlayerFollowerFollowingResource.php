<?php

namespace App\Http\Resources\Api\App;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerFollowerFollowingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return Helper::playerListingResource($this,$request);
    }
}
