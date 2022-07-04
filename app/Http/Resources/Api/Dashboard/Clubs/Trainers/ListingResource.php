<?php
namespace App\Http\Resources\Api\Dashboard\Clubs\Trainers;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'image' => $this->profile_picture ?? "",
            'teams' => count($this->teams_trainers->pluck('team_name')->toArray()) > 0 ? implode(', ', $this->teams_trainers->pluck('team_name')->toArray()) : "",
            'isOwner' => $this->id == $request->ownerId ? 'Yes' : 'No',
            'lastActive' => !empty($this->last_seen) ? date('d-m-Y', strtotime($this->last_seen)) : '-',
            'status' => $this->status->name
        ];
    }
}