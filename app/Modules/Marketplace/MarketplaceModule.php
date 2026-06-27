<?php

namespace App\Modules\Marketplace;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\Marketplace\Pages\OverviewPage;
use Filament\Panel;

class MarketplaceModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'marketplace';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/Marketplace'),
            for: 'App\\Modules\\Marketplace',
        );
    }

    public static function homePage(): string
    {
        return OverviewPage::class;
    }
}
