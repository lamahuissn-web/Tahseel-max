<?php

namespace App\Http\Resources\subscriptions\member;

use App\Http\Resources\Admin\MembersResource;
use App\Http\Resources\subscriptions\TrainerResource;
use App\Models\Members;
use App\Models\Trainers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberDietResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'date'=>$this->date,
            'breakfast_choice1'=>$this->break_fast_choice1,
            'breakfast_choice2'=>$this->break_fast_choice2,

            'snack_choice1'=>$this->snake_choice1,
            'snack_choice2'=>$this->snake_choice2,

            'lunch_choice1'=>$this->lunch_choice1,
            'lunch_choice2'=>$this->lunch_choice2,

            'before_training_choice1' =>$this->before_training_choice1,
            'before_training_choice2' =>$this->before_training_choice2,

            'after_training_choice1' =>$this->after_training_choice1,
            'after_training_choice2' =>$this->after_training_choice2,
            'dinner_choice1' => $this->dinner_choice1,
            'dinner_choice2' => $this->dinner_choice2,
            'diet_name' => $this->diet_name,
            'daily_share' => $this->daily_share,
            'member'         => new MembersResource(Members::where('id', $this->members_id)->first()),
            'trainer'        => new TrainerResource(Trainers::where('id', $this->trainers_id)->first()),

        ];
    }
}
