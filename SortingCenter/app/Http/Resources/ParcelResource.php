<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParcelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tracking_no' => $this->tracking_no,
            'status' => $this->status,
            'address' => $this->address,
            'seller_name' => $this->seller_name,
            'carrier' => $this->carrier,
            'area' => $this->whenLoaded('area', fn () => [
                'id' => $this->area->id,
                'name' => $this->area->name,
                'code' => $this->area->code,
            ]),
            'rider' => $this->whenLoaded('rider', fn () => [
                'id' => $this->rider->id,
                'name' => $this->rider->name,
                'initials' => $this->rider->initials,
            ]),
            'dropped_off_by' => $this->whenLoaded('droppedOffBy', fn () => [
                'id' => $this->droppedOffBy->id,
                'name' => $this->droppedOffBy->name,
                'initials' => $this->droppedOffBy->initials,
            ]),
            'eta_note' => $this->eta_note,
            'scanned_undelivered' => $this->scanned_undelivered,
            'sla_due_at' => $this->sla_due_at?->toIso8601String(),
            'is_overdue' => $this->isOverdue(),
            'overdue_minutes' => $this->overdueMinutes(),
            'overdue_label' => $this->overdueLabel(),
            'scan_count' => $this->effectiveScanCount(),
            'expected_scans' => $this->expectedScans(),
            'received_at' => $this->received_at?->toIso8601String(),
            'sorted_at' => $this->sorted_at?->toIso8601String(),
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
        ];
    }
}
