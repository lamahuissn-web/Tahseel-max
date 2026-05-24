<?php

namespace App\Http\Resources\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VideoResource extends JsonResource
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
            'Ivideo' => $this->id,
            'videoTitle' => $this->title,
            'videoLink' => $this->full_link,
            'videoLinkid' => $this->link_id,
            'videoImage' => $imageurl,
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
            'full_link' => $this->full_link,
            'link_id' => $this->link_id,
            'Image' => $imageurl,
        ];
    }
}
