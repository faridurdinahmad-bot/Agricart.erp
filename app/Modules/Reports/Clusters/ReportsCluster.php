<?php

namespace App\Modules\Reports\Clusters;

use App\Core\Filament\Clusters\BaseModuleCluster;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Support\Icons\Heroicon;

class ReportsCluster extends BaseModuleCluster
{
    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = ModuleNavigationSort::REPORTS;

    protected static ?string $slug = 'reports';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $clusterBreadcrumb = 'Reports';
}
