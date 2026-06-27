<?php

namespace App\Modules\Inventory\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Inventory\Clusters\InventoryCluster;
use App\Modules\Inventory\Navigation\InventoryNavigation;
use Filament\Support\Icons\Heroicon;

class ReorderPage extends BaseModulePage
{
    protected static ?string $cluster = InventoryCluster::class;

    protected static ?string $navigationLabel = 'Reorder';

    protected static ?string $title = 'Reorder';

    protected static ?string $slug = 'reorder';

    protected static ?int $navigationSort = InventoryNavigation::REORDER;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedArrowPath;
}
