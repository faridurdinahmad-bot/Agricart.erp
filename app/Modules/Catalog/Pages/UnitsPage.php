<?php

namespace App\Modules\Catalog\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Catalog\Clusters\CatalogCluster;
use App\Modules\Catalog\Navigation\CatalogNavigation;
use Filament\Support\Icons\Heroicon;

class UnitsPage extends BaseModulePage
{
    protected static ?string $cluster = CatalogCluster::class;

    protected static ?string $navigationLabel = 'Units';

    protected static ?string $title = 'Units';

    protected static ?string $slug = 'units';

    protected static ?int $navigationSort = CatalogNavigation::UNITS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;
}
