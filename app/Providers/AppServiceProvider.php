<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('app.settings', function () {
            return Cache::rememberForever('settings', function () {
                try {
                    return Setting::all()->pluck('value', 'key');
                } catch (QueryException $e) {
                    return collect();
                }
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
