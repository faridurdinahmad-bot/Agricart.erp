<?php

namespace App\Modules\Reports\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Reports\Clusters\ReportsCluster;
use App\Modules\Reports\Navigation\ReportsNavigation;
use Filament\Support\Icons\Heroicon;

class CustomerLedgerPage extends BaseModulePage
{
    protected static ?string $cluster = ReportsCluster::class;

    protected static ?string $navigationLabel = 'Customer Ledger';

    protected static ?string $title = 'Customer Ledger';

    protected static ?string $slug = 'customer-ledger';

    protected static ?int $navigationSort = ReportsNavigation::CUSTOMER_LEDGER;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;
}
