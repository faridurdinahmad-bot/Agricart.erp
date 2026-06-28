<?php

namespace App\Modules\Logistics\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Logistics\Clusters\LogisticsCluster;
use App\Modules\Logistics\Navigation\LogisticsNavigation;
use Filament\Support\Icons\Heroicon;

class OverviewPage extends BaseModulePage
{
    protected static ?string $cluster = LogisticsCluster::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Overview';

    protected static ?string $slug = 'overview';

    protected static ?int $navigationSort = LogisticsNavigation::OVERVIEW;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
}
