<?php

namespace App\Http\Resources\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EventImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);

        if (!empty($this->image)) {
            $image_path = Storage::disk('images')->url($this->image);

            $imageurl = asset((Storage::disk('images')->exists($this->image)) ? $image_path : 'assets/images/blank.png');
        } else {
            $imageurl = asset('assets/images/blank.png');

        }
        if (!empty($this->thumbnailsm)) {
            $thumbnailsm_path = Storage::disk('images')->url($this->thumbnailsm);

            $thumbnailsm = asset((Storage::disk('images')->exists($this->thumbnailsm)) ? $thumbnailsm_path : 'assets/images/blank.png');
        } else {
            $thumbnailsm = asset('assets/images/blank.png');

        }

        if (!empty($this->thumbnailmd)) {
            $thumbnailmd_path = Storage::disk('images')->url($this->thumbnailmd);

            $thumbnailmd= asset((Storage::disk('images')->exists($this->thumbnailmd)) ? $thumbnailmd_path : 'assets/images/blank.png');
        }else{
            $thumbnailmd= asset('assets/images/blank.png');

        }
        return [
            'image' => $imageurl,
            'thumbnailsm' => $thumbnailsm,
            'thumbnailmd' => $thumbnailmd,
        ];
    }

}
