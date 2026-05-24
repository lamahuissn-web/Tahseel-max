<?php

namespace App\Http\Resources\mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DataAppResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        if (!empty($this->main_image)) {
            $image_path = Storage::disk('images')->url($this->main_image);

            $imageurl = asset((Storage::disk('images')->exists($this->main_image)) ? $image_path : 'assets/images/blank.png');
        } else {
            $imageurl = asset('assets/images/blank.png');

        }
        return [
            'email' => $this->email,
            'location' => $this->maplocation,
            'phone' => $this->phone,
            'Discount_ratio' => $this->discount_ratio,
            'transport' => $this->transport_value,
            'Image' => $imageurl,
            "title"=>$this->name,
            "address"=>$this->address,
            "short_description"=>$this->description,
        ];
    }
}
