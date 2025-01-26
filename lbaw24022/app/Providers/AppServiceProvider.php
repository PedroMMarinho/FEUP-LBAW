<?php

namespace App\Providers;

use App\Observers\AuctionObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use App\Models\Auction;

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
        Blade::if('admin', function () {
            return Auth::check() && Auth::user()->role === 'Admin';
        });

        Blade::if('regularuser', function () {
            return Auth::check() && Auth::user()->role === 'Regular User';
        });
        Blade::if('expert', function () {
            return Auth::check() && Auth::user()->role === 'Expert';
        });

        Auction::observe(AuctionObserver::class);
    }
}
