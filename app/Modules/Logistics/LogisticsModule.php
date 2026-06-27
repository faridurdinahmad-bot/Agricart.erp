<?php

namespace App\Modules\Logistics;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\Logistics\Pages\OverviewPage;
use Filament\Panel;

class LogisticsModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'logistics';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/Logistics'),
            for: 'App\\Modules\\Logistics',
        );
    }

    public static function homePage(): string
    {
        return OverviewPage::class;
    }
}
