<?php

namespace App\Modules\Sales;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\Sales\Pages\OverviewPage;
use Filament\Panel;

class SalesModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'sales';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/Sales'),
            for: 'App\\Modules\\Sales',
        );
    }

    public static function homePage(): string
    {
        return OverviewPage::class;
    }
}
