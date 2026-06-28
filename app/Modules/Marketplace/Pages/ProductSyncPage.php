<?php

namespace App\Modules\Marketplace\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Marketplace\Clusters\MarketplaceCluster;
use App\Modules\Marketplace\Navigation\MarketplaceNavigation;
use Filament\Support\Icons\Heroicon;

class ProductSyncPage extends BaseModulePage
{
    protected static ?string $cluster = MarketplaceCluster::class;

    protected static ?string $navigationLabel = 'Product Sync';

    protected static ?string $title = 'Product Sync';

    protected static ?string $slug = 'product-sync';

    protected static ?int $navigationSort = MarketplaceNavigation::PRODUCT_SYNC;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;
}
