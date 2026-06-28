<?php

namespace App\Modules\Settings\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Support\Icons\Heroicon;

class OverviewPage extends BaseModulePage
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Overview';

    protected static ?string $slug = 'overview';

    protected static ?int $navigationSort = SettingsNavigation::OVERVIEW;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
}
