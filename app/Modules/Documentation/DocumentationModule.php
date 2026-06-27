<?php

namespace App\Modules\Documentation;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\Documentation\Pages\OverviewPage;
use Filament\Panel;

class DocumentationModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'documentation';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/Documentation'),
            for: 'App\\Modules\\Documentation',
        );
    }

    public static function homePage(): string
    {
        return OverviewPage::class;
    }
}
