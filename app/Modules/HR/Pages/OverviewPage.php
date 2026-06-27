<?php

namespace App\Modules\HR\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\HR\Clusters\HRCluster;
use App\Modules\HR\Navigation\HRNavigation;
use Filament\Support\Icons\Heroicon;

class OverviewPage extends BaseModulePage
{
    protected static ?string $cluster = HRCluster::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Overview';

    protected static ?string $slug = 'overview';

    protected static ?int $navigationSort = HRNavigation::OVERVIEW;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedSquares2x2;
}
