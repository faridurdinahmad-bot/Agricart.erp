<?php

namespace App\Modules\Accounts\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Accounts\Clusters\AccountsCluster;
use App\Modules\Accounts\Navigation\AccountsNavigation;
use Filament\Support\Icons\Heroicon;

class FinancialReportsPage extends BaseModulePage
{
    protected static ?string $cluster = AccountsCluster::class;

    protected static ?string $navigationLabel = 'Financial Reports';

    protected static ?string $title = 'Financial Reports';

    protected static ?string $slug = 'financial-reports';

    protected static ?int $navigationSort = AccountsNavigation::FINANCIAL_REPORTS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
}
