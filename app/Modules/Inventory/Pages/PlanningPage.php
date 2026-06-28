<?php

namespace App\Modules\Inventory\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Inventory\Clusters\InventoryCluster;
use App\Modules\Inventory\Navigation\InventoryNavigation;
use Filament\Support\Icons\Heroicon;

class PlanningPage extends BaseModulePage
{
    protected static ?string $cluster = InventoryCluster::class;

    protected static ?string $navigationLabel = 'Planning';

    protected static ?string $title = 'Planning';

    protected static ?string $slug = 'planning';

    protected static ?int $navigationSort = InventoryNavigation::PLANNING;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
}
