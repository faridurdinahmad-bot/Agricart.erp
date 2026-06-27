<?php

namespace App\Modules\Approvals\Clusters;

use App\Core\Filament\Clusters\BaseModuleCluster;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Support\Icons\Heroicon;

class ApprovalsCluster extends BaseModuleCluster
{
    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = ModuleNavigationSort::APPROVALS;

    protected static ?string $slug = 'approvals';

    protected static ?string $navigationLabel = 'Approvals';

    protected static ?string $clusterBreadcrumb = 'Approvals';
}
