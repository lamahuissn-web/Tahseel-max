<?php

namespace App\Http\Resources\subscriptions\member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceExercisesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);

        return [
            'ExercisesNum' => $this->id,
            'deviceCode' => optional($this->device_code_data)->code,
            'Name' => $this->name,
            'GroupsNum' => $this->groups,
            'Level' => $this->exercise_level,
            'numbers' => $this->numbers,
            'fullLink' => $this->link,
            'linkId' => $this->link_id,
            'duration' => 30,
            'image' => asset('assets/images/blank.png')
        ];
    }
}
