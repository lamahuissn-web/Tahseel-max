<?php

namespace App\Http\Resources\mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberInbodyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'inbodyId'=>$this->id,
            'register_date'=>$this->date,
            'height'=>$this->height,
            'weight'=>$this->weight,
            'fat_percentage'=>$this->fat_percentage,
            'muscle_mass_percentage'=>$this->muscle_mass_percentage,
            'body_status'=>$this->body_status,
        ];
    }
}
