<?php

namespace App\Modules\Store\Clusters;

use App\Core\Filament\Clusters\BaseModuleCluster;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Support\Icons\Heroicon;

class StoreCluster extends BaseModuleCluster
{
    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?int $navigationSort = ModuleNavigationSort::STORE;

    protected static ?string $slug = 'store';

    protected static ?string $navigationLabel = 'Online Store';

    protected static ?string $clusterBreadcrumb = 'Online Store';
}
