<?php

namespace App\Modules\Logistics\Clusters;

use App\Core\Filament\Clusters\BaseModuleCluster;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Support\Icons\Heroicon;

class LogisticsCluster extends BaseModuleCluster
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = ModuleNavigationSort::LOGISTICS;

    protected static ?string $slug = 'logistics';

    protected static ?string $navigationLabel = 'Logistics';

    protected static ?string $clusterBreadcrumb = 'Logistics';
}
