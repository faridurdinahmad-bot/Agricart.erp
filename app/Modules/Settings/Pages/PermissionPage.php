<?php

namespace App\Modules\Settings\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Support\Icons\Heroicon;

class PermissionPage extends BaseModulePage
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Permission';

    protected static ?string $title = 'Permission';

    protected static ?string $slug = 'permission';

    protected static ?int $navigationSort = SettingsNavigation::PERMISSION;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedShieldCheck;
}
