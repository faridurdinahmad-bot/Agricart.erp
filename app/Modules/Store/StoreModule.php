<?php

namespace App\Modules\Store;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\Store\Pages\OverviewPage;
use Filament\Panel;

class StoreModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'store';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/Store'),
            for: 'App\\Modules\\Store',
        );
    }

    public static function homePage(): string
    {
        return OverviewPage::class;
    }
}
