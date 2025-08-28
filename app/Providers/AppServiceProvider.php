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
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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

        // === RATE LIMITING CONFIGURATION ===
        $this->configureRateLimiting();
        // === AKHIR VIEW COMPOSER ===
    }

    /**
     * Configure rate limiting untuk aplikasi
     */
    protected function configureRateLimiting(): void
    {
        // Rate limit untuk form submissions (prevent double submit)
        RateLimiter::for('submit', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limit untuk API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limit untuk login attempts
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Rate limit untuk web routes
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}