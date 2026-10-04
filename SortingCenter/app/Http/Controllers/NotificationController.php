<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Return latest 15 notifications as JSON for the dropdown */
    public function index()
    {
        $notifications = Activity::with(['parcel', 'rider'])
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn ($a) => [
                'id'          => $a->id,
                'description' => $a->description,
                'icon'        => $a->icon,
                'icon_classes'=> $a->iconClasses(),
                'icon_svg'    => $a->iconSvg(),
                'read'        => $a->read_at !== null,
                'time'        => $a->created_at->diffForHumans(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => Activity::unread()->count(),
        ]);
    }

    /** Mark a single notification as read */
    public function markRead(Activity $activity)
    {
        $activity->markAsRead();
        return response()->json(['ok' => true]);
    }

    /** Mark all notifications as read */
    public function markAllRead()
    {
        Activity::unread()->update(['read_at' => now()]);
        return response()->json(['ok' => true, 'unread_count' => 0]);
    }
}
