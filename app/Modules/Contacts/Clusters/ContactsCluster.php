<?php

namespace App\Modules\Contacts\Clusters;

use App\Core\Filament\Clusters\BaseModuleCluster;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Support\Icons\Heroicon;

class ContactsCluster extends BaseModuleCluster
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = ModuleNavigationSort::CONTACTS;

    protected static ?string $slug = 'contacts';

    protected static ?string $navigationLabel = 'Contacts';

    protected static ?string $clusterBreadcrumb = 'Contacts';
}
