<?php

namespace App\Modules\Settings\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Support\Icons\Heroicon;

class StoreSettingPage extends BaseModulePage
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Store Setting';

    protected static ?string $title = 'Store Setting';

    protected static ?string $slug = 'store-setting';

    protected static ?int $navigationSort = SettingsNavigation::STORE_SETTING;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBuildingStorefront;
}
