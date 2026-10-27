<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    /**
     * Return grouped actionable alerts plus the latest activity feed.
     */
    public function index(): JsonResponse
    {
        $payload = $this->notifications->payload();

        return response()->json([
            'notifications' => $payload['notifications'],
            'unread_count' => $payload['unread_count'],
            'actionable_count' => $this->notifications->countActionable(),
        ]);
    }

    /**
     * Mark a single activity notification as read.
     */
    public function markRead(Activity $activity): JsonResponse
    {
        $activity->markAsRead();

        return response()->json([
            'ok' => true,
            'message' => 'Notification marked as read.',
            'unread_count' => Activity::unread()->count(),
        ]);
    }

    /**
     * Mark every activity notification as read. Actionable parcel/KYC alerts
     * stay visible until their underlying records are resolved.
     */
    public function markAllRead(): JsonResponse
    {
        Activity::unread()->update(['read_at' => now()]);

        return response()->json([
            'ok' => true,
            'message' => 'All activity notifications marked as read.',
            'unread_count' => $this->notifications->countActionable(),
        ]);
    }
}
