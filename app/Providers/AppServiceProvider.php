<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\TokenPolicy;
use Rupadana\ApiService\Models\Token;
use Illuminate\Support\Facades\Gate;
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
        // 1. Wajib di Laravel 11: Hubungkan Model Token Vendor dengan TokenPolicy Aplikasi
        Gate::policy(Token::class, TokenPolicy::class);

        // 2. Bypass seluruh hak akses untuk Super Admin & Admin
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super_admin') || $user->isAdmin()) {
                return true;
            }
        });

        // Fitur Pulse
        Gate::define('viewPulse', function (User $user) {
            return $user->isAdmin();
        });
    }
}
