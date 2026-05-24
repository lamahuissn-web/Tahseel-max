<?php

namespace App\Http\Resources\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
  /*  public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }*/

    public function toArray(Request $request): array
    {

        if (!empty($this->main_image)) {
            $image_path = Storage::disk('images')->url($this->main_image);

            $imageurl = asset((Storage::disk('images')->exists($this->main_image)) ? $image_path : 'assets/images/blank.png');
        } else {
            $imageurl = asset('assets/images/blank.png');

        }
        return [
            'IBlog' => $this->id,
            'blogTitle' => $this->title,
            'blogDetails' => $this->details,
            'blogDate' => formatDateDayDisplay($this->date_at),
            'blogPublisher' => $this->publisher,
            'blogImage' => $imageurl,
            'blogImgaes' => BlogImageResource::collection($this->images)
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
        $details = $this->getTranslations('details');
        return [
            'id' => $this->id,
            'title_en' => $title['en'],
            'title_ar' => $title['ar'],
            'details_en' => $details['en'],
            'details_ar' => $details['ar'],
            'date_at' => $this->date_at,
            'publisher' => $this->publisher,
            'blogImage' => $imageurl,
            'imgaes' => BlogImageResource::collection($this->images)
        ];
    }
}
