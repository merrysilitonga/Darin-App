<?php

namespace App\Providers;

use Carbon\Carbon;
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
        /*
         * Nama hari & bulan pada translatedFormat() memakai bahasa Indonesia
         * (mis. "Rabu, 11 November 2026"). Hanya memengaruhi Carbon,
         * tidak mengubah APP_LOCALE / pesan validasi Laravel.
         */
        Carbon::setLocale('id');
    }
}