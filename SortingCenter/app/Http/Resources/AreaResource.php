<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'chart_color' => $this->chart_color,
            'pickup_window' => $this->pickup_window,
            'cutoff_time' => $this->cutoff_time,
            'pickup_status' => $this->pickup_status,
            'parcel_count' => $this->whenLoaded('parcels', fn () => $this->parcels->count(), $this->parcels_count ?? 0),
            'rider' => $this->whenLoaded('rider', fn () => $this->rider ? [
                'id' => $this->rider->id,
                'name' => $this->rider->name,
                'initials' => $this->rider->initials,
                'status' => $this->rider->status,
            ] : null),
        ];
    }
}
