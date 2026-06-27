<?php

namespace App\Modules\Marketplace\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Marketplace\Clusters\MarketplaceCluster;
use App\Modules\Marketplace\Navigation\MarketplaceNavigation;
use Filament\Support\Icons\Heroicon;

class OrderSyncPage extends BaseModulePage
{
    protected static ?string $cluster = MarketplaceCluster::class;

    protected static ?string $navigationLabel = 'Order Sync';

    protected static ?string $title = 'Order Sync';

    protected static ?string $slug = 'order-sync';

    protected static ?int $navigationSort = MarketplaceNavigation::ORDER_SYNC;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;
}
