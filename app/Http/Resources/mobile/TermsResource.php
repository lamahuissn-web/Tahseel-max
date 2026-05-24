<?php

namespace App\Http\Resources\mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TermsResource extends JsonResource
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
        return [
            'MainAddress' => $this->address,
            'SecondAddress' => $this->sub_address,
            'Details' => $this->details,
//            'Image' => $imageurl,
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
        $address = $this->getTranslations('address');
        $details = $this->getTranslations('details');
        $sub_address = $this->getTranslations('sub_address');
        return [
            'id' => $this->id,
            'address_en' => optional($address)['en'],
            'address_ar' => optional($address)['ar'],
            'details_en' => optional($details)['en'],
            'details_ar' => optional($details)['ar'],
            'sub_address_ar' => optional($sub_address)['ar'],
            'sub_address_en' => optional($sub_address)['en'],
            'Image' => $imageurl,
        ];
    }


}
