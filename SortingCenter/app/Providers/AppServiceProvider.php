<?php

namespace App\Providers;

use App\Models\HubProfile;
use App\Services\LogSmsSender;
use App\Services\MetricsService;
use App\Services\NotificationService;
use App\Services\ParcelStatusService;
use App\Services\SmsSender;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsSender::class, LogSmsSender::class);
    }

    public function boot(): void
    {
        // Supply nav_counts, attention, and activity timeline to all views
        View::composer('*', function ($view) {
            $metrics = app(MetricsService::class);
            $statusService = app(ParcelStatusService::class);
            $view->with([
                'nav_counts' => $metrics->getNavCounts(),
                'attention' => $metrics->getAttention(),
                'activity' => $statusService->getActivityFeed(10),
            ]);
        });

        // Share hub profile + unread notifications with every view using the layout
        View::composer('layouts.sorting', function ($view) {
            $hub = HubProfile::current();
            $notifications = app(NotificationService::class)->payload();

            $view->with([
                'unreadNotifCount' => $notifications['unread_count'],
                'notifications' => $notifications['notifications'],
                'hubProfile' => $hub,
            ]);
        });

        // Also share with the sidebar partial directly
        View::composer('partials.sidebar', function ($view) {
            $view->with('hubProfile', HubProfile::current());
        });
    }
}
