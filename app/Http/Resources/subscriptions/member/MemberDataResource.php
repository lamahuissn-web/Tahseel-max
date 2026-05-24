<?php

namespace App\Http\Resources\subscriptions\member;

use App\Http\Resources\Admin\MembersResource;
use App\Http\Resources\mobile\MemberGoalsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MemberDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Handle member image

        if (!empty($this->member_image)) {
            $image_path = Storage::disk('images')->url($this->member_image);
            $imageurl = asset((Storage::disk('images')->exists($this->member_image)) ? $image_path : 'assets/images/blank.png');
        } else {
            $imageurl = asset('assets/images/blank.png');
        }

        // Get the first and last inbody records
        $firstInbody = $this->inbody->first();
        $lastInbody = $this->inbody->last();

        // Initialize variables
        $weightLoss = null;
        $remainingWeight = null;
        $firstInbodyWeight = $firstInbody ? $firstInbody['weight'] : null;

        if ($firstInbody && $lastInbody) {
            $weightLoss = $firstInbody['weight'] - $lastInbody['weight'];
        }

        if ($this->target_weight && $lastInbody) {
            $remainingWeight = $lastInbody['weight'] - $this->target_weight;
        }
        $inbodyData = $this->inbody->map(function ($inbody) {
            return [
                'date' => $inbody->date,
                'weight' => $inbody->weight,
                'fat_percentage' => $inbody->fat_percentage,
                'muscle_mass_percentage' => $inbody->muscle_mass_percentage,
            ];
        });



        $currentDate = now()->format('Y-m-d');
        $currentSubscription = $this->members_subscriptions->filter(function ($subscription) use ($currentDate) {
            return $subscription->end_date > $currentDate;
        })->first();
        $SubscriptionsCount = $this->members_subscriptions->count();

        return [
            'memberId' => $this->id,
            'memberName' => $this->member_name,
            'memberEmail' => $this->email,
            'memberPhone' => $this->phone,
            'country_code' => $this->country_code,
            'phone_full' => $this->phone_full,
            'memberBirthDate' => $this->birth_date,
            'memberHealthStatus' => $this->health_status_id,
            'memberContractBnod' => $this->contract_bnod,
            'memberImage' => $imageurl,
            'memberTargetWeight' => $this->target_weight,
            'firstInbodyWeight' => $firstInbodyWeight,
            'weightLoss' => $weightLoss,
            'remainingWeight' => $remainingWeight,
            'inbody' => $inbodyData,
            'currentSubscription' => $currentSubscription,
            'SubscriptionsCount' => $SubscriptionsCount,
            'memberDiet' => new MemberGoalsResource($this->diet),
        ];
    }

}
