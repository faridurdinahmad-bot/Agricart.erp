<?php

namespace App\Modules\HR\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\HR\Clusters\HRCluster;
use App\Modules\HR\Navigation\HRNavigation;
use Filament\Support\Icons\Heroicon;

class StaffPage extends BaseModulePage
{
    protected static ?string $cluster = HRCluster::class;

    protected static ?string $navigationLabel = 'Staff';

    protected static ?string $title = 'Staff';

    protected static ?string $slug = 'staff';

    protected static ?int $navigationSort = HRNavigation::STAFF;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
}
