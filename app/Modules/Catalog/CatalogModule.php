<?php

namespace App\Modules\Catalog;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\Catalog\Pages\OverviewPage;
use Filament\Panel;

class CatalogModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'catalog';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/Catalog'),
            for: 'App\\Modules\\Catalog',
        );
    }

    public static function homePage(): string
    {
        return OverviewPage::class;
    }
}
