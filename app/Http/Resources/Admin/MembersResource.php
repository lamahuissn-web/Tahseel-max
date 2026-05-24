<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\mobile\MemberGoalsResource;
use App\Http\Resources\mobile\MemberInbodyResource;
use App\Models\MembersGoals;
use App\Models\MembersInbody;
use App\Models\subscriptions\SubscriptionSettings_M;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MembersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (!empty($this->member_image)) {
            $image_path = Storage::disk('images')->url($this->member_image);
            $imageurl = asset((Storage::disk('images')->exists($this->member_image)) ? $image_path : 'assets/images/blank.png');
        } else {
            $imageurl = asset('assets/images/blank.png');

        }

       $member_goals= MembersGoals::where('member_id',$this->id)->get();
        $lastinbody = $this->inbody ? new MemberInbodyResource(MembersInbody::where('member_id',$this->id)->orderBy('date', 'desc')->first()) : null;
        $last_two_inbody = MembersInbody::where('member_id', $this->id)
            ->orderBy('date', 'desc')
            ->take(2)
            ->get(['weight']);

        if ($last_two_inbody->count() == 2) {
            $weight_difference = $last_two_inbody[0]->weight - $last_two_inbody[1]->weight;
        } else {
            $weight_difference = 0;
        }
        return [
          'memberId'=>$this->id,
          'memberName'=>$this->member_name,
          'memberEmail'=>$this->email,
          'memberPhone'=>$this->phone,
          'country_code'=>$this->country_code,
          'phone_full'=>$this->phone_full,
          'memberBirthDate'=>$this->birth_date,
          'memberHeight'=>$this->height ? $this->height:0.00,
          'memberWeight'=>$this->weight ? $this->weight :0.00 ,
          'memberFatPercentage'=>$this->fat_percentage ? $this->fat_percentage :0.00,
          'memberTargetWeight'=>$this->target_weight ?$this->target_weight : 0.00 ,
          'memberHealthStatus'=>$this->health_status_id,
          'memberContractBnod'=>$this->contract_bnod,
          'memberImage'=>$imageurl,
          'lastInbody'=>$lastinbody,
          'memberGoalData' => $this->member_goals ? MemberGoalsResource::collection($this->member_goals) : null,
            'LosesWeight'=>$weight_difference,
            'MemberInviteCode'=>$this->invite_code,

        ];
    }


    public function edite_data($request): array
    {

        if (!empty($this->member_image)) {
            $image_path = Storage::disk('images')->url($this->member_image);
            //dd($image_path);
            $imageurl = asset((Storage::disk('images')->exists($this->member_image)) ? $image_path : 'assets/images/blank.png');

        } else {
            $imageurl = asset('assets/images/blank.png');

        }

        return [
            'memberId'=>$this->id,
            'memberName'=>$this->member_name,
            'memberEmail'=>$this->email,
            'memberPhone'=>$this->phone,
            'memberGoal'=>$this->goals->pluck('goal_id')->toArray(),
            'memberGoalData'=>$this->goals,
            'memberBirthDate'=>$this->birth_date,
            'memberHeight'=>$this->height,
            'memberWeight'=>$this->weight,
            'memberFatPercentage'=>$this->fat_percentage ? $this->fat_percentage :0.00,
            'memberTargetWeight'=>$this->target_weight,
            'memberHealthStatus'=>$this->health_status_id,
            'memberContractBnod'=>$this->contract_bnod,
            'memberImage'=>$imageurl,
            'country_code'=>$this->country_code,
            'phone_full'=>$this->phone_full,
        ];
    }
}
