<?php

namespace App\Modules\HR\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\HR\Clusters\HRCluster;
use App\Modules\HR\Navigation\HRNavigation;
use Filament\Support\Icons\Heroicon;

class LeavePage extends BaseModulePage
{
    protected static ?string $cluster = HRCluster::class;

    protected static ?string $navigationLabel = 'Leave';

    protected static ?string $title = 'Leave';

    protected static ?string $slug = 'leave';

    protected static ?int $navigationSort = HRNavigation::LEAVE;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedCalendarDays;
}
