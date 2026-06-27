<?php

namespace App\Modules\Settings\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Support\Icons\Heroicon;

class SystemPage extends BaseModulePage
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'System';

    protected static ?string $title = 'System';

    protected static ?string $slug = 'system';

    protected static ?int $navigationSort = SettingsNavigation::SYSTEM;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedServerStack;
}
