<?php

namespace App\Http\Resources\subscriptions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpecialSubscriptionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'Duration' => $this->duration,
            'price' => $this->price,
            'max_discount' => $this->max_discount,
            'exercise' => $this->exercise_name->title,
            'total_cost' => $this->price-($this->price*$this->max_discount/100),
        ];
    }
}
