<?php

namespace App\Modules\Marketplace\Clusters;

use App\Core\Filament\Clusters\BaseModuleCluster;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Support\Icons\Heroicon;

class MarketplaceCluster extends BaseModuleCluster
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?int $navigationSort = ModuleNavigationSort::MARKETPLACE;

    protected static ?string $slug = 'marketplace';

    protected static ?string $navigationLabel = 'Marketplace';

    protected static ?string $clusterBreadcrumb = 'Marketplace';
}
