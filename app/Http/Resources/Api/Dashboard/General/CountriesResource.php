<?php
namespace App\Http\Resources\Api\Dashboard\General;
use Illuminate\Http\Resources\Json\JsonResource;

class CountriesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}