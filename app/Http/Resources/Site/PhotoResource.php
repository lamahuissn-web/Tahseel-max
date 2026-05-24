<?php

namespace App\Http\Resources\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PhotoResource extends JsonResource
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
            'Iphoto' => $this->id,
            'photoTitle' => $this->title,
            'photoDetails' => $this->details,
            'photoImage' => $imageurl,
            'photoImgaes' => PhotoImageResource::collection($this->images)
        ];
    }
    public function edite_data($request): array
    {

        if (!empty($this->main_image)) {
            $image_path = Storage::disk('images')->url($this->main_image);

            $imageurl = asset((Storage::disk('images')->exists($this->main_image)) ? $image_path : 'assets/images/blank.png');
        } else {
            $imageurl = asset('assets/images/blank.png');

        }
        $title = $this->getTranslations('title');

        return [
            'id' => $this->id,
            'title_en' => optional($title)['en'],
            'title_ar' => optional($title)['ar'],
            'photoImage' => $imageurl,
            'imgaes' => BlogImageResource::collection($this->images)
        ];
    }
}
