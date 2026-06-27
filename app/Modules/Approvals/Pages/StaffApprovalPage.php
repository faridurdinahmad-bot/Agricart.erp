<?php

namespace App\Modules\Approvals\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Approvals\Clusters\ApprovalsCluster;
use App\Modules\Approvals\Navigation\ApprovalsNavigation;
use Filament\Support\Icons\Heroicon;

class StaffApprovalPage extends BaseModulePage
{
    protected static ?string $cluster = ApprovalsCluster::class;

    protected static ?string $navigationLabel = 'Staff Approval';

    protected static ?string $title = 'Staff Approval';

    protected static ?string $slug = 'staff-approval';

    protected static ?int $navigationSort = ApprovalsNavigation::STAFF_APPROVAL;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedUserGroup;
}
