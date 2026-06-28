<?php

namespace App\Modules\Reports\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Reports\Clusters\ReportsCluster;
use App\Modules\Reports\Navigation\ReportsNavigation;
use Filament\Support\Icons\Heroicon;

class ExpenseReportPage extends BaseModulePage
{
    protected static ?string $cluster = ReportsCluster::class;

    protected static ?string $navigationLabel = 'Expense Report';

    protected static ?string $title = 'Expense Report';

    protected static ?string $slug = 'expense-report';

    protected static ?int $navigationSort = ReportsNavigation::EXPENSE_REPORT;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
}
