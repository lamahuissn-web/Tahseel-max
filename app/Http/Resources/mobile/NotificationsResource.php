<?php

namespace App\Http\Resources\mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class NotificationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {


        if ($this->type != 'class') {
            if (!empty($this->from_user->user_image)) {
                $image_path = Storage::disk('images')->url($this->from_user->user_image);

                $image = asset((Storage::disk('images')->exists($this->from_user->user_image)) ? $image_path : 'assets/media/avatars/blank.png');
            } else {
                $image = asset('assets/media/avatars/blank.png');

            }
        } else {
            if (!empty($this->from_user_trainer->user_image)) {
                $image_path = Storage::disk('images')->url($this->from_user_trainer->user_image);

                $image = asset((Storage::disk('images')->exists($this->from_user_trainer->user_image)) ? $image_path : 'assets/media/avatars/blank.png');
            } else {
                $image = asset('assets/media/avatars/blank.png');

            }

        }

        return [
            'title' => $this->title,
            'content' => $this->content,
            'from_user' => $this->from_user,
            'from_user_image' => $image,
//             'from_user_data'=>new UserResource($this->from_user),
            'type' => $this->type,
            'status' => $this->status,

            'time' => $this->add_at_time,
            'day' => $this->add_at_day,

        ];
    }
}
