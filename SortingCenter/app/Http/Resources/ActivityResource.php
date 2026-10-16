<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'description' => $this->description,
            'icon'        => $this->icon,
            'is_read'     => !$this->isUnread(),
            'time'        => $this->created_at->diffForHumans(),
            'created_at'  => $this->created_at->toIso8601String(),
            'parcel_id'   => $this->parcel_id,
            'rider_id'    => $this->rider_id,
        ];
    }
}
