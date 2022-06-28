<?php
namespace App\Http\Resources\Api\Dashboard\Events;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'engTitle' => $this->engTitle,
            'color' => $this->color,
            'status' => $this->status
        ];
    }
}