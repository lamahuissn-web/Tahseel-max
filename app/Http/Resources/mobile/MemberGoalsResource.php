<?php

namespace App\Http\Resources\mobile;

use App\Models\subscriptions\SubscriptionSettings_M;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberGoalsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
           'goal'=>SubscriptionSettings_M::find($this->goal_id)->title,
        ];
    }
}
