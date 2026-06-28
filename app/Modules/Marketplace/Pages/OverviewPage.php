<?php

namespace App\Modules\Marketplace\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Marketplace\Clusters\MarketplaceCluster;
use App\Modules\Marketplace\Navigation\MarketplaceNavigation;
use Filament\Support\Icons\Heroicon;

class OverviewPage extends BaseModulePage
{
    protected static ?string $cluster = MarketplaceCluster::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Overview';

    protected static ?string $slug = 'overview';

    protected static ?int $navigationSort = MarketplaceNavigation::OVERVIEW;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
}
