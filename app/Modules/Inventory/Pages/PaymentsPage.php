<?php

namespace App\Modules\Inventory\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Inventory\Clusters\InventoryCluster;
use App\Modules\Inventory\Navigation\InventoryNavigation;
use Filament\Support\Icons\Heroicon;

class PaymentsPage extends BaseModulePage
{
    protected static ?string $cluster = InventoryCluster::class;

    protected static ?string $navigationLabel = 'Payments';

    protected static ?string $title = 'Payments';

    protected static ?string $slug = 'payments';

    protected static ?int $navigationSort = InventoryNavigation::PAYMENTS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedCreditCard;
}
