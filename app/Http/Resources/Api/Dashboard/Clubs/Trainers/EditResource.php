<?php
namespace App\Http\Resources\Api\Dashboard\Clubs\Trainers;
use App\Helpers\Helper;
use App\Http\Resources\Api\Dashboard\General\CountryCodesResource;
use App\Http\Resources\Api\Dashboard\Clubs\Teams\ListingResource as TeamsListingResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EditResource extends JsonResource
{
    public function toArray($request)
    {
        return Helper::trainerResource($this,$request,false);
    }
}