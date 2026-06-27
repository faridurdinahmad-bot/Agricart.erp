<?php

namespace App\Modules\Settings;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\Settings\Pages\OverviewPage;
use Filament\Panel;

class SettingsModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'settings';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/Settings'),
            for: 'App\\Modules\\Settings',
        );
    }

    public static function homePage(): string
    {
        return OverviewPage::class;
    }
}
