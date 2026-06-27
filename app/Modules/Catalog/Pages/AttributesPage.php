<?php

namespace App\Modules\Catalog\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Catalog\Clusters\CatalogCluster;
use App\Modules\Catalog\Navigation\CatalogNavigation;
use Filament\Support\Icons\Heroicon;

class AttributesPage extends BaseModulePage
{
    protected static ?string $cluster = CatalogCluster::class;

    protected static ?string $navigationLabel = 'Attributes';

    protected static ?string $title = 'Attributes';

    protected static ?string $slug = 'attributes';

    protected static ?int $navigationSort = CatalogNavigation::ATTRIBUTES;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;
}
