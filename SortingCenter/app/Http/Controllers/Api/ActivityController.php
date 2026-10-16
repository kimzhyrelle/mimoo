<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /** GET /api/activities */
    public function index(Request $request): JsonResponse
    {
        $activities = Activity::latest()
            ->limit($request->limit ?? 20)
            ->get();

        return response()->json([
            'data' => ActivityResource::collection($activities),
            'meta' => [
                'unread_count' => Activity::unread()->count(),
            ],
        ]);
    }

    /** POST /api/activities/{activity}/read */
    public function markRead(Activity $activity): JsonResponse
    {
        $activity->markAsRead();

        return response()->json(['message' => 'Marked as read.']);
    }

    /** POST /api/activities/read-all */
    public function markAllRead(): JsonResponse
    {
        Activity::whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['message' => 'All activities marked as read.']);
    }
}
