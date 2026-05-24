<?php

namespace App\Http\Resources\mobile;

use App\Http\Resources\Admin\MembersResource;
use App\Models\Members;
use App\Models\MembersSubscriptions;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerMembersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
      $member=  MembersSubscriptions::where('id',$this->member_subscription_id)->first();
      if ($member)
      {
          $member_id =$member->member_id;
      }else{
          $member_id=null;
      }
        return [
            'member'=>$member_id ?  new MembersResource(Members::where('id',$member_id)->first()) : [],
        ];
    }
}
