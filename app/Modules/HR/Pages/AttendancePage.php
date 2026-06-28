<?php

namespace App\Modules\HR\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\HR\Clusters\HRCluster;
use App\Modules\HR\Navigation\HRNavigation;
use Filament\Support\Icons\Heroicon;

class AttendancePage extends BaseModulePage
{
    protected static ?string $cluster = HRCluster::class;

    protected static ?string $navigationLabel = 'Attendance';

    protected static ?string $title = 'Attendance';

    protected static ?string $slug = 'attendance';

    protected static ?int $navigationSort = HRNavigation::ATTENDANCE;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;
}
