<?php

namespace App\Modules\Catalog\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Catalog\Clusters\CatalogCluster;
use App\Modules\Catalog\Navigation\CatalogNavigation;
use Filament\Support\Icons\Heroicon;

class CategoriesPage extends BaseModulePage
{
    protected static ?string $cluster = CatalogCluster::class;

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $title = 'Categories';

    protected static ?string $slug = 'categories';

    protected static ?int $navigationSort = CatalogNavigation::CATEGORIES;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;
}
