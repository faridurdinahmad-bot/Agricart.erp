<?php

namespace App\Modules\Catalog\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Catalog\Clusters\CatalogCluster;
use App\Modules\Catalog\Navigation\CatalogNavigation;
use Filament\Support\Icons\Heroicon;

class OverviewPage extends BaseModulePage
{
    protected static ?string $cluster = CatalogCluster::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Overview';

    protected static ?string $slug = 'overview';

    protected static ?int $navigationSort = CatalogNavigation::OVERVIEW;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
}
