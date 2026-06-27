<?php

namespace App\Modules\Accounts;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\Accounts\Pages\OverviewPage;
use Filament\Panel;

class AccountsModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'accounts';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/Accounts'),
            for: 'App\\Modules\\Accounts',
        );
    }

    public static function homePage(): string
    {
        return OverviewPage::class;
    }
}
