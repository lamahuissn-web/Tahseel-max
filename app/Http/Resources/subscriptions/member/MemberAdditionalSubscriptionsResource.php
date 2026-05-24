<?php

namespace App\Http\Resources\subscriptions\member;

use App\Http\Resources\subscriptions\SpecialSubscriptionsResource;
use App\Http\Resources\subscriptions\TrainerResource;
use App\Models\subscriptions\SpecialSubscription_M;
use App\Models\Trainers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberAdditionalSubscriptionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'subscription' =>new SpecialSubscriptionsResource(SpecialSubscription_M::where('id', $this->subscription_id)->first()),
            'type' => $this->type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'trainer_id' => $this->trainer_id,
            'trainer' => new TrainerResource(Trainers::where('id',$this->trainer_id)->first()),
            'cost' => $this->cost,
            'discount' => $this->discount,

        ];
    }
}
