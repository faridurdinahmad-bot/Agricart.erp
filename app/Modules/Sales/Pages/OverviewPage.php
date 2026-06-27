<?php

namespace App\Modules\Sales\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Sales\Clusters\SalesCluster;
use App\Modules\Sales\Navigation\SalesNavigation;
use Filament\Support\Icons\Heroicon;

class OverviewPage extends BaseModulePage
{
    protected static ?string $cluster = SalesCluster::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Overview';

    protected static ?string $slug = 'overview';

    protected static ?int $navigationSort = SalesNavigation::OVERVIEW;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedSquares2x2;
}
