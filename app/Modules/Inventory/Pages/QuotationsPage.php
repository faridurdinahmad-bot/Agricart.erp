<?php

namespace App\Modules\Inventory\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Inventory\Clusters\InventoryCluster;
use App\Modules\Inventory\Navigation\InventoryNavigation;
use Filament\Support\Icons\Heroicon;

class QuotationsPage extends BaseModulePage
{
    protected static ?string $cluster = InventoryCluster::class;

    protected static ?string $navigationLabel = 'Quotations';

    protected static ?string $title = 'Quotations';

    protected static ?string $slug = 'quotations';

    protected static ?int $navigationSort = InventoryNavigation::QUOTATIONS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
}
