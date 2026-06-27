<?php

namespace App\Modules\Approvals;

use App\Core\Modules\Contracts\ModuleInterface;
use App\Modules\Approvals\Pages\StaffApprovalPage;
use Filament\Panel;

class ApprovalsModule implements ModuleInterface
{
    public static function id(): string
    {
        return 'approvals';
    }

    public static function registerPanel(Panel $panel): void
    {
        $panel->discoverClusters(
            in: app_path('Modules/Approvals'),
            for: 'App\\Modules\\Approvals',
        );
    }

    public static function homePage(): string
    {
        return StaffApprovalPage::class;
    }
}
