<?php

namespace App\Modules\Catalog\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Catalog\Clusters\CatalogCluster;
use App\Modules\Catalog\Navigation\CatalogNavigation;
use Filament\Support\Icons\Heroicon;

class ControlsPage extends BaseModulePage
{
    protected static ?string $cluster = CatalogCluster::class;

    protected static ?string $navigationLabel = 'Controls';

    protected static ?string $title = 'Controls';

    protected static ?string $slug = 'controls';

    protected static ?int $navigationSort = CatalogNavigation::CONTROLS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedAdjustmentsVertical;
}
