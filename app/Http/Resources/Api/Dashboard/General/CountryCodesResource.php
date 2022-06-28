<?php
namespace App\Http\Resources\Api\Dashboard\General;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryCodesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->phone_code
        ];
    }
}