<?php

namespace App\Modules\Catalog\Clusters;

use App\Core\Filament\Clusters\BaseModuleCluster;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Support\Icons\Heroicon;

class CatalogCluster extends BaseModuleCluster
{
    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = ModuleNavigationSort::CATALOG;

    protected static ?string $slug = 'catalog';

    protected static ?string $navigationLabel = 'Catalog';

    protected static ?string $clusterBreadcrumb = 'Catalog';
}
