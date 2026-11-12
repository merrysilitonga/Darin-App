<?php

namespace App\Providers;

use App\Services\MentorPendingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Dihitung sekali per request walau dipakai sidebar & layout mentor.
        $this->app->scoped(MentorPendingService::class);
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

        /*
         * Badge notifikasi mentor (logbook & izin menunggu) untuk sidebar
         * dan header layout mentor.
         */
        View::composer(['layouts.sidebar', 'layouts.mentor-layout'], function ($view) {
            $user = Auth::user();

            if (!$user || strtolower(trim((string) $user->role)) !== 'mentor') {
                return;
            }

            $view->with(
                'mentorPending',
                $this->app->make(MentorPendingService::class)->summary($user)
            );
        });
    }
}
