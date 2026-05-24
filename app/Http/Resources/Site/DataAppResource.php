<?php

namespace App\Http\Resources\Site;

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
            'Title' => $this->getTranslations('name'),
            'email' => $this->email,
            'address' => $this->getTranslations('address'),
            'location' => $this->maplocation,
            'phone' => $this->phone,
            'short_description' => $this->getTranslations('description'),

//            'short_about' => $this->getTranslations('short_about'),
//            'video_link' => $this->video,
            'Discount_ratio' => $this->discount_ratio,
            'Image' => $imageurl,

            "loc_title"=>$this->name,
            "loc_address"=>$this->address,
            "loc_short_description"=>$this->description,
        ];
    }
}
