<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Parcel;
use App\Models\ShipmentEvent;
use App\Models\ShipmentNotification;
use App\Services\ShipmentStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShipmentController extends Controller
{
    public function tracking(string $trackingNo): JsonResponse
    {
        $parcel = $this->findByTracking($trackingNo);

        return response()->json([
            'ok' => true,
            'data' => [
                'tracking_no' => $parcel->tracking_no,
                'status' => $parcel->status,
                'status_label' => $parcel->statusLabel(),
                'buyer_name' => $parcel->buyer_name,
                'rider' => $parcel->rider?->name,
                'eta_note' => $parcel->eta_note,
                'delivery_attempts' => (int) $parcel->delivery_attempts,
                'failure_reason' => $parcel->failure_reason,
                'events' => $parcel->shipmentEvents()
                    ->orderBy('occurred_at')
                    ->get()
                    ->map(fn (ShipmentEvent $event) => [
                        'status' => $event->to_status,
                        'label' => $parcel->statusLabelFor($event->to_status),
                        'reason' => $event->reason,
                        'at' => $event->occurred_at?->toIso8601String(),
                    ]),
            ],
        ]);
    }

    public function verifyOtp(Request $request, string $trackingNo, ShipmentStatusService $shipments): JsonResponse
    {
        $data = $request->validate(['code' => 'required|digits:6']);
        $parcel = $this->findByTracking($trackingNo);

        try {
            $shipments->confirmDeliveryOtp($parcel, $data['code']);
        } catch (ValidationException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }

        return response()->json(['ok' => true, 'message' => 'Delivery code confirmed.']);
    }

    public function events(Parcel $parcel): JsonResponse
    {
        $events = $parcel->shipmentEvents()->orderBy('occurred_at')->get();

        return response()->json([
            'ok' => true,
            'data' => $events->map(fn (ShipmentEvent $event) => [
                'from' => $event->from_status,
                'to' => $event->to_status,
                'actor_role' => $event->actor_role,
                'reason' => $event->reason,
                'at' => $event->occurred_at?->toIso8601String(),
            ]),
        ]);
    }

    public function notifications(): JsonResponse
    {
        $items = ShipmentNotification::orderByDesc('created_at')->limit(100)->get();

        return response()->json(['ok' => true, 'data' => $items]);
    }

    public function auditLogs(): JsonResponse
    {
        $items = AuditLog::orderByDesc('created_at')->limit(200)->get();

        return response()->json(['ok' => true, 'data' => $items]);
    }

    private function findByTracking(string $trackingNo): Parcel
    {
        $normalized = ltrim($trackingNo, '#');

        return Parcel::with('rider')
            ->whereIn('tracking_no', array_unique([$trackingNo, $normalized, '#'.$normalized]))
            ->firstOrFail();
    }
}
