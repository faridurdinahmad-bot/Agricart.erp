<?php

namespace App\Modules\Contacts;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\Contacts\Pages\OverviewPage;
use Filament\Panel;

class ContactsModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'contacts';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/Contacts'),
            for: 'App\\Modules\\Contacts',
        );
    }

    public static function homePage(): string
    {
        return OverviewPage::class;
    }
}
