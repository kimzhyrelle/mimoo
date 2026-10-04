<?php

namespace App\Providers;

use App\Models\Activity;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Share unread notification count with every view that uses the layout
        View::composer('layouts.sorting', function ($view) {
            $view->with('unreadNotifCount', Activity::unread()->count());
        });
    }
}
