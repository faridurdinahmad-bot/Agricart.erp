<?php

namespace App\Providers;

use App\Core\Authorization\PermissionGate;
use App\Core\Modules\ModuleRegistry;
use App\Http\Responses\AgricartLoginResponse;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LoginResponse::class, AgricartLoginResponse::class);

        ModuleRegistry::discover();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        PermissionGate::syncIfStale();
    }
}
