<?php

namespace App\Http\Resources\subscriptions\member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'Num' => $this->id,
            'trainer_nmae' => optional($this->trainers->employee)->name,
            'class_name' => optional($this->class_data)->title,
            'time' => date('h:i a',strtotime($this->time)),
            'day' => $this->date,
            'duration' => 30,
            'edite_data' => (request()->has('ScheduleNum')) ? $this->edite() : null,

        ];
    }

    function edite(){
        return [
            'Num' => $this->id,
            'trainer' => $this->trainer_id,
            'class' => $this->class_id,
            'time' => date('h:i a',strtotime($this->time)),
            'day' => $this->date,
            'duration' => 30,

        ];
    }
}
