<?php

namespace App\Modules\Reports;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\Reports\Pages\OverviewPage;
use Filament\Panel;

class ReportsModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'reports';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/Reports'),
            for: 'App\\Modules\\Reports',
        );
    }

    public static function homePage(): string
    {
        return OverviewPage::class;
    }
}
