<?php

namespace App\Modules\Reports\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Reports\Clusters\ReportsCluster;
use App\Modules\Reports\Navigation\ReportsNavigation;
use Filament\Support\Icons\Heroicon;

class SupplierLedgerPage extends BaseModulePage
{
    protected static ?string $cluster = ReportsCluster::class;

    protected static ?string $navigationLabel = 'Supplier Ledger';

    protected static ?string $title = 'Supplier Ledger';

    protected static ?string $slug = 'supplier-ledger';

    protected static ?int $navigationSort = ReportsNavigation::SUPPLIER_LEDGER;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;
}
