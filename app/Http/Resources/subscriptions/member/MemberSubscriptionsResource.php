<?php

namespace App\Http\Resources\subscriptions\member;


use App\Http\Resources\mobile\MainSubscriptionResource;
use App\Http\Resources\subscriptions\TrainerResource;
use App\Models\AdditionalMemberSubscriptions;
use App\Models\subscriptions\MainSubscription_M;
use App\Models\Trainers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberSubscriptionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'process_num' => $this->process_num,
            'process_date' => $this->process_date,
            'subscription' => new MainSubscriptionResource(MainSubscription_M::where('id', $this->subscription_id)->first()),
            'transport' => $this->transport,
            'payMethod' => $this->pay_method,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'discount' => $this->discount,
            'transport_value' => $this->transport_value,
            'transport_duration' => $this->transport_duration,
            'package_price' => $this->package_price,
            'package_duration' => $this->package_duration,
            'total_cost' => $this->total_cost,
            'notes' => $this->notes,
            'additional_subscription' => MemberAdditionalSubscriptionsResource::collection(AdditionalMemberSubscriptions::where('member_subscription_id', $this->id)->get()
            ),
        ];
    }

}
