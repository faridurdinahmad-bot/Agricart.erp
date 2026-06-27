<?php

namespace App\Modules\HR;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\HR\Pages\OverviewPage;
use Filament\Panel;

class HRModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'hr';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/HR'),
            for: 'App\\Modules\\HR',
        );
    }

    public static function homePage(): string
    {
        return OverviewPage::class;
    }
}
