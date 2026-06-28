<?php

namespace App\Modules\HR\Clusters;

use App\Core\Filament\Clusters\BaseModuleCluster;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Support\Icons\Heroicon;

class HRCluster extends BaseModuleCluster
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?int $navigationSort = ModuleNavigationSort::HR;

    protected static ?string $slug = 'hr';

    protected static ?string $navigationLabel = 'HR';

    protected static ?string $clusterBreadcrumb = 'HR';
}
