<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\RoleServices;
use App\Services\RoleServicesInterface;
use App\Services\TourService;
use App\Services\TourServiceInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TourServiceInterface::class, TourService::class);
        $this->app->bind(RoleServicesInterface::class, RoleServices::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', static function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too Many Attempts',
                    ], 429);
                });
        });
    }
}
