<?php

namespace App\Modules\Sales\Clusters;

use App\Core\Filament\Clusters\BaseModuleCluster;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Support\Icons\Heroicon;

class SalesCluster extends BaseModuleCluster
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = ModuleNavigationSort::SALES;

    protected static ?string $slug = 'sales';

    protected static ?string $navigationLabel = 'Sales';

    protected static ?string $clusterBreadcrumb = 'Sales';
}
