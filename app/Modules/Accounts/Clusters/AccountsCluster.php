<?php

namespace App\Modules\Accounts\Clusters;

use App\Core\Filament\Clusters\BaseModuleCluster;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Support\Icons\Heroicon;

class AccountsCluster extends BaseModuleCluster
{
    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?int $navigationSort = ModuleNavigationSort::ACCOUNTS;

    protected static ?string $slug = 'accounts';

    protected static ?string $navigationLabel = 'Accounts';

    protected static ?string $clusterBreadcrumb = 'Accounts';
}
