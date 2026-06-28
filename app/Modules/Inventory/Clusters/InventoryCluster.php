<?php

namespace App\Modules\Inventory\Clusters;

use App\Core\Filament\Clusters\BaseModuleCluster;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Support\Icons\Heroicon;

class InventoryCluster extends BaseModuleCluster
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = ModuleNavigationSort::INVENTORY;

    protected static ?string $slug = 'inventory';

    protected static ?string $navigationLabel = 'Inventory';

    protected static ?string $clusterBreadcrumb = 'Inventory';
}
