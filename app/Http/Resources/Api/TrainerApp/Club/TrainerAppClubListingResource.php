<?php
namespace App\Http\Resources\Api\TrainerApp\Club;
use App\SelectedClub;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerAppClubListingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->title,
            'is_selected'=>SelectedClub::where("club_id",$this->id)->where("trainer_user_id",auth()->user()->id)->first() ? 1 : 0
        ];
    }
}