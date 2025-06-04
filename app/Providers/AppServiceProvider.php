<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // Import View facade
use Illuminate\Support\Facades\Auth; // Import Auth facade
// use Illuminate\Pagination\Paginator; 

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
        // Paginator::useBootstrapFive(); 

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
        // === AKHIR VIEW COMPOSER ===
    }
}