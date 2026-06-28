<?php

namespace App\Modules\Inventory\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Inventory\Clusters\InventoryCluster;
use App\Modules\Inventory\Navigation\InventoryNavigation;
use Filament\Support\Icons\Heroicon;

class PurchasesPage extends BaseModulePage
{
    protected static ?string $cluster = InventoryCluster::class;

    protected static ?string $navigationLabel = 'Purchases';

    protected static ?string $title = 'Purchases';

    protected static ?string $slug = 'purchases';

    protected static ?int $navigationSort = InventoryNavigation::PURCHASES;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;
}
