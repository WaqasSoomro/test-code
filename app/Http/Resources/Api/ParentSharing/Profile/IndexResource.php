<?php
namespace App\Http\Resources\Api\ParentSharing\Profile;
use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'permissions' => Helper::getPermissions($this->getRoleNames()[0]),
            'token' => $this->token
        ];
    }
}