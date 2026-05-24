<?php

namespace App\Http\Resources\subscriptions\member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ExercisesNum' => $this->id,
            'day' => $this->moving_day,
            'time' => date('h:i a',strtotime($this->moving_time)),
            'persons_number' => $this->persons_number,

        ];    }
}
