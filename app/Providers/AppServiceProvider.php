<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\StockMovement;
use App\Observers\StockMovementObserver;
use Illuminate\Pagination\Paginator;
use App\Models\Maintenance;
use App\Observers\MaintenanceObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive(); 

        // === VIEW COMPOSER UNTUK NOTIFIKASI DI NAVBAR ===
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) { 
                $unreadNotifications = Auth::user()->unreadNotifications()->take(5)->get(); 
                $unreadNotificationsCount = Auth::user()->unreadNotifications()->count();
                
                $view->with('unreadNotifications', $unreadNotifications)
                     ->with('unreadNotificationsCount', $unreadNotificationsCount);
            } else {
                $view->with('unreadNotifications', collect()) 
                     ->with('unreadNotificationsCount', 0);
            }
        });

        StockMovement::observe(StockMovementObserver::class);
        Maintenance::observe(MaintenanceObserver::class);
        // === AKHIR VIEW COMPOSER ===
    }
}