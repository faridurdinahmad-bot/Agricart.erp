<?php

namespace App\Modules\Reports\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Reports\Clusters\ReportsCluster;
use App\Modules\Reports\Navigation\ReportsNavigation;
use Filament\Support\Icons\Heroicon;

class CashReportPage extends BaseModulePage
{
    protected static ?string $cluster = ReportsCluster::class;

    protected static ?string $navigationLabel = 'Cash Report';

    protected static ?string $title = 'Cash Report';

    protected static ?string $slug = 'cash-report';

    protected static ?int $navigationSort = ReportsNavigation::CASH_REPORT;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedCurrencyDollar;
}
