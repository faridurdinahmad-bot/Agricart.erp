<?php

namespace App\Modules\Catalog\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Catalog\Clusters\CatalogCluster;
use App\Modules\Catalog\Navigation\CatalogNavigation;
use Filament\Support\Icons\Heroicon;

class LabelsPage extends BaseModulePage
{
    protected static ?string $cluster = CatalogCluster::class;

    protected static ?string $navigationLabel = 'Labels';

    protected static ?string $title = 'Labels';

    protected static ?string $slug = 'labels';

    protected static ?int $navigationSort = CatalogNavigation::LABELS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedTicket;
}
