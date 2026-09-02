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
        // Boshqaruv paneli administratori uchun barcha policy'lar ochiq;
        // policy'lar App\Models\User ni kutadi, Admin boshqa model.
        \Illuminate\Support\Facades\Gate::before(function ($user) {
            return $user instanceof \App\Models\Admin ? true : null;
        });

        // Har bir ko'rinishda $biz — joriy biznes turi (so'zlar va ikonkalar uchun).
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with('biz', \App\Support\Business::current());
        });

        //
    }
}
