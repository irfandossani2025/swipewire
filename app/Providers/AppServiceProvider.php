<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
     *
     * Production runs MariaDB 10.1, whose 767-byte InnoDB index limit rejects
     * utf8mb4 VARCHAR(255) unique/primary keys, so indexed strings are capped at 191.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer('layouts.store', function (\Illuminate\View\View $view): void {
            $view->with('siteSettings', once(fn () => SiteSetting::query()->pluck('value', 'key')));
        });
    }
}
