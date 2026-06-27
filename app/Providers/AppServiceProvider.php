<?php

namespace App\Providers;

use App\Core\Modules\ModuleRegistry;
use App\Modules\Settings\SettingsModule;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        ModuleRegistry::register(SettingsModule::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
