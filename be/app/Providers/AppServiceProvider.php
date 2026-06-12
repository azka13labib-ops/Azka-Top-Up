<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Contracts\Interfaces\data\GameInterface;
use App\Repositories\GameRepository;
use App\Contracts\Interfaces\data\ProductInterface;
use App\Repositories\ProductRepository;
use App\Contracts\Interfaces\data\OrderInterface;
use App\Repositories\OrderRepository;
use App\Contracts\Interfaces\data\OrderLogInterface;
use App\Repositories\OrderLogRepository;
use App\Contracts\Interfaces\data\AdminUserInterface;
use App\Repositories\AdminUserRepository;
use App\Contracts\Interfaces\data\SiteSettingInterface;
use App\Repositories\SiteSettingRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GameInterface::class, GameRepository::class);
        $this->app->bind(ProductInterface::class, ProductRepository::class);
        $this->app->bind(OrderInterface::class, OrderRepository::class);
        $this->app->bind(OrderLogInterface::class, OrderLogRepository::class);
        $this->app->bind(AdminUserInterface::class, AdminUserRepository::class);
        $this->app->bind(SiteSettingInterface::class, SiteSettingRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Lapisan 1: Rate Limiters (Anti-Brute Force & Spam) ─────────────

        // Customer register: 5 attempt per 15 menit per IP
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinutes(15, 5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Terlalu banyak percobaan registrasi. Silakan coba lagi dalam 15 menit.',
                        'errors'  => [],
                    ], 429);
                });
        });

        // Customer login: 5 attempt per 15 menit per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinutes(15, 5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.',
                        'errors'  => [],
                    ], 429);
                });
        });

        // Admin login: lebih ketat — 3 attempt per 15 menit per IP
        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinutes(15, 3)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Terlalu banyak percobaan login admin. Silakan coba lagi dalam 15 menit.',
                        'errors'  => [],
                    ], 429);
                });
        });

        // Create order: 10 per menit per user (atau IP jika guest)
        RateLimiter::for('create-order', function (Request $request) {
            $key = $request->user()?->id ?? $request->ip();
            return Limit::perMinute(10)
                ->by($key)
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Terlalu banyak order dalam waktu singkat. Silakan tunggu sebentar.',
                        'errors'  => [],
                    ], 429);
                });
        });

        // Public API (games, products): 60 per menit per IP
        RateLimiter::for('public-api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->ip());
        });
    }
}

