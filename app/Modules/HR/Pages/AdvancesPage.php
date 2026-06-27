<?php

namespace App\Modules\HR\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\HR\Clusters\HRCluster;
use App\Modules\HR\Navigation\HRNavigation;
use Filament\Support\Icons\Heroicon;

class AdvancesPage extends BaseModulePage
{
    protected static ?string $cluster = HRCluster::class;

    protected static ?string $navigationLabel = 'Advances';

    protected static ?string $title = 'Advances';

    protected static ?string $slug = 'advances';

    protected static ?int $navigationSort = HRNavigation::ADVANCES;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;
}
