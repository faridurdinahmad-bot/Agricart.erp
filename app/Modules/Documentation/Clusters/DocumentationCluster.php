<?php

namespace App\Modules\Documentation\Clusters;

use App\Core\Filament\Clusters\BaseModuleCluster;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Support\Icons\Heroicon;

class DocumentationCluster extends BaseModuleCluster
{
    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = ModuleNavigationSort::DOCUMENTATION;

    protected static ?string $slug = 'documentation';

    protected static ?string $navigationLabel = 'Documentation';

    protected static ?string $clusterBreadcrumb = 'Documentation';
}
