<?php

namespace App\Modules\Settings\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Support\Icons\Heroicon;

class BackupsPage extends BaseModulePage
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Backups';

    protected static ?string $title = 'Backups';

    protected static ?string $slug = 'backups';

    protected static ?int $navigationSort = SettingsNavigation::BACKUPS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedCloudArrowUp;
}
