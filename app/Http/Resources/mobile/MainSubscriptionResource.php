<?php

namespace App\Http\Resources\mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Lang;

class MainSubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /*if (!empty($this->image)) {
                $image_path = Storage::disk('images')->url($this->image);

                $imageurl = asset((Storage::disk('images')->exists($this->image)) ? $image_path : 'assets/images/blank.png');
            } else {
                $imageurl = asset('assets/images/blank.png');

            }*/
        $Details_tag = $this->details_tag;
        $Details_tag = json_decode($Details_tag);
        $sub_type_arr = [
            'monthly' => trans('sub.monthly'),
            'quarter' => trans('sub.quarter'),
            'semi' => trans('sub.semi'),
            'annual' => trans('sub.annual'),
        ];

        $category = $sub_type_arr[$this->category];
        return [
            'id' => $this->id,
            'title' => $this->name,
            'Details' => $this->details,
            'Details_text' => strip_tags($this->details),
//            'Details_tag' => $this->getTranslations('details_tag'),
            'Details_tag' => $Details_tag,
            'Duration' => $this->duration,
            'price' => $this->price,
            'category' => $category,
            'max_discount' => $this->max_discount,
            'total_main_cost' =>$this->price - ($this->price * $this->max_discount/100),
//            'Image' => $imageurl,
        ];
    }
}
