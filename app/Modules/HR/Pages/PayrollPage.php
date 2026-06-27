<?php

namespace App\Modules\HR\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\HR\Clusters\HRCluster;
use App\Modules\HR\Navigation\HRNavigation;
use Filament\Support\Icons\Heroicon;

class PayrollPage extends BaseModulePage
{
    protected static ?string $cluster = HRCluster::class;

    protected static ?string $navigationLabel = 'Payroll';

    protected static ?string $title = 'Payroll';

    protected static ?string $slug = 'payroll';

    protected static ?int $navigationSort = HRNavigation::PAYROLL;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedCurrencyDollar;
}
