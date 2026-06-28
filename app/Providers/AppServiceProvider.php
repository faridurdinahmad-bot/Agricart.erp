<?php

namespace App\Providers;

use App\Core\Ai\Services\AiService;
use App\Core\Ai\Support\AiResponseParser;
use App\Core\Authorization\PermissionGate;
use App\Core\Modules\ModuleRegistry;
use App\Http\Responses\AgricartLoginResponse;
use App\Modules\Catalog\Services\CategoryAiContentService;
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

        $this->app->singleton(AiService::class);
        $this->app->singleton(AiResponseParser::class);
        $this->app->singleton(CategoryAiContentService::class);

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
