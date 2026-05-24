<?php

namespace App\Http\Resources\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        if (!empty($this->image)) {
            $image_path = Storage::disk('images')->url($this->image);

            $imageurl = asset((Storage::disk('images')->exists($this->image)) ? $image_path : 'assets/images/blank.png');
        } else {
            $imageurl = asset('assets/images/blank.png');

        }
        return [
            'name' => $this->name,
            'jop_title' => $this->jop_title,
            'description' => $this->description,
            'phone' => $this->phone,
            'email' => $this->email,
            'Image' => $imageurl,
        ];
    }

    public function edite_data($request): array
    {

        if (!empty($this->image)) {
            $image_path = Storage::disk('images')->url($this->image);

            $imageurl = asset((Storage::disk('images')->exists($this->image)) ? $image_path : 'assets/images/blank.png');
        } else {
            $imageurl = asset('assets/images/blank.png');

        }
        $name = $this->getTranslations('name');
        $description = $this->getTranslations('description');
        $jop_title = $this->getTranslations('jop_title');
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'email' => $this->email,
            'name_en' => optional($name)['en'],
            'name_ar' => optional($name)['ar'],
            'description_en' => optional($description)['en'],
            'description_ar' => optional($description)['ar'],
            'jop_title_ar' => optional($jop_title)['ar'],
            'jop_title_en' => optional($jop_title)['en'],
            'Image' => $imageurl,
        ];
    }

}
