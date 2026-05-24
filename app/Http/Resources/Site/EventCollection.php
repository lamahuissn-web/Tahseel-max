<?php

namespace App\Http\Resources\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EventCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
          return [
            'data' => parent::toArray($request),

              'current_page' => $this->currentPage(),
              'first_page_url' => $this->url(1),
              'from' => $this->firstItem(),
              'last_page' => $this->lastPage(),
              'last_page_url' => $this->url($this->lastPage()),
              'next_page_url' => $this->nextPageUrl(),
              'path' => $this->path(),
              'per_page' => $this->perPage(),
              'prev_page_url' => $this->previousPageUrl(),
              'to' => $this->lastItem(),
              'total' => $this->total(),
              'request'=>$request->all(),

          ];
    }
}
