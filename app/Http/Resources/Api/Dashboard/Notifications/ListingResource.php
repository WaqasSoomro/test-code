<?php
namespace App\Http\Resources\Api\Dashboard\Notifications;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'recordId' => $this->model_type_id,
            'type' => $this->model_type,
            'description' => $this->description,
            'actionType' => $this->click_action,
            'notificationFrom' => [
                'id' => $this->receiver->id,
                'firstName' => $this->receiver->first_name,
                'lastName' => $this->receiver->last_name
            ]
        ];
    }
}