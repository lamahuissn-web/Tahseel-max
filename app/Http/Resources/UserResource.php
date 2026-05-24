<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\MembersResource;
use App\Models\Master;
use App\Models\Members;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'UserId'         => $this->id,
            'UserName'       => $this->name,
            'UserEmail'       => $this->email,
            'UserPhone'      => $this->phone,
            'financial_transactions_sum_amount' => $this->financialTransactions->sum('amount'),
            'currency' => get_app_config_data('currency'),
            'phone_service' => get_app_config_data('phone_service'),
            'roleId' => $this->roles()->first()?->id,
            'roleName' => $this->roles()->first()?->name,
        ];
    }
}
