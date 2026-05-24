<?php

namespace App\Http\Resources\subscriptions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DevicesResource extends JsonResource
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
            'code' => $this->code,
            'exercise_type' => $this->exercise_type,
            'description'=>$this->description,
            'image' => $imageurl,
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
      //  $name = $this->getTranslations('name');
       // $feedback = $this->getTranslations('feedback');
       // $jop_title = $this->getTranslations('jop_title');
        return [
           
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'exercise_type' => $this->exercise_type,
            'description'=>$this->description,
            'image' => $imageurl,
        ];
    }

}
