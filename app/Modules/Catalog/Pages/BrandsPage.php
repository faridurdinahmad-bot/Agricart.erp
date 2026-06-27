<?php

namespace App\Modules\Catalog\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Catalog\Clusters\CatalogCluster;
use App\Modules\Catalog\Navigation\CatalogNavigation;
use Filament\Support\Icons\Heroicon;

class BrandsPage extends BaseModulePage
{
    protected static ?string $cluster = CatalogCluster::class;

    protected static ?string $navigationLabel = 'Brands';

    protected static ?string $title = 'Brands';

    protected static ?string $slug = 'brands';

    protected static ?int $navigationSort = CatalogNavigation::BRANDS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedTag;
}
