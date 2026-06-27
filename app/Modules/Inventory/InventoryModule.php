<?php

namespace App\Modules\Inventory;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\Inventory\Pages\OverviewPage;
use Filament\Panel;

class InventoryModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'inventory';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/Inventory'),
            for: 'App\\Modules\\Inventory',
        );
    }

    public static function homePage(): string
    {
        return OverviewPage::class;
    }
}
