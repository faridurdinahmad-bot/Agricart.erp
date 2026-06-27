<?php

namespace App\Modules\Settings\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Support\Icons\Heroicon;

class PrintingPage extends BaseModulePage
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Printing';

    protected static ?string $title = 'Printing';

    protected static ?string $slug = 'printing';

    protected static ?int $navigationSort = SettingsNavigation::PRINTING;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedPrinter;
}
