<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'initials'     => $this->initials,
            'status'       => $this->status,
            'status_label' => $this->statusLabel(),
            'is_active'    => $this->isActive(),
            'vehicle_type' => $this->vehicle_type,
            'plate_no'     => $this->plate_no,
            'vehicle_label'=> $this->vehicleLabel(),
            'area'         => $this->whenLoaded('area', fn () => $this->area ? [
                'id'    => $this->area->id,
                'name'  => $this->area->name,
                'code'  => $this->area->code,
                'color' => $this->area->chart_color,
            ] : null),
        ];
    }
}
