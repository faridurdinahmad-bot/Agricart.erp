<?php

namespace App\Modules\Inventory\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Inventory\Clusters\InventoryCluster;
use App\Modules\Inventory\Navigation\InventoryNavigation;
use Filament\Support\Icons\Heroicon;

class PriceTagsPage extends BaseModulePage
{
    protected static ?string $cluster = InventoryCluster::class;

    protected static ?string $navigationLabel = 'Price Tags';

    protected static ?string $title = 'Price Tags';

    protected static ?string $slug = 'price-tags';

    protected static ?int $navigationSort = InventoryNavigation::PRICE_TAGS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedCurrencyDollar;
}
