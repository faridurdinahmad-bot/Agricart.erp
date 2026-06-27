<?php

namespace App\Modules\Catalog\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Catalog\Clusters\CatalogCluster;
use App\Modules\Catalog\Navigation\CatalogNavigation;
use Filament\Support\Icons\Heroicon;

class ProductsPage extends BaseModulePage
{
    protected static ?string $cluster = CatalogCluster::class;

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $title = 'Products';

    protected static ?string $slug = 'products';

    protected static ?int $navigationSort = CatalogNavigation::PRODUCTS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedCube;
}
