<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\HubProfile;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Share hub profile + unread notifications with every view using the layout
        View::composer('layouts.sorting', function ($view) {
            $hub = HubProfile::current();
            $view->with([
                'unreadNotifCount' => Activity::unread()->count(),
                'hubProfile'       => $hub,
            ]);
        });

        // Also share with the sidebar partial directly
        View::composer('partials.sidebar', function ($view) {
            $view->with('hubProfile', HubProfile::current());
        });
    }
}
