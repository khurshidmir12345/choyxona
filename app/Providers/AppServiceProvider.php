<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Har bir ko'rinishda $biz — joriy biznes turi (so'zlar va ikonkalar uchun).
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with('biz', \App\Support\Business::current());
        });

        //
    }
}
