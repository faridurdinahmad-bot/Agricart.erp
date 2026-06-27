<?php

namespace App\Modules\Logistics\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Logistics\Clusters\LogisticsCluster;
use App\Modules\Logistics\Navigation\LogisticsNavigation;
use Filament\Support\Icons\Heroicon;

class CouriersPage extends BaseModulePage
{
    protected static ?string $cluster = LogisticsCluster::class;

    protected static ?string $navigationLabel = 'Couriers';

    protected static ?string $title = 'Couriers';

    protected static ?string $slug = 'couriers';

    protected static ?int $navigationSort = LogisticsNavigation::COURIERS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedUserGroup;
}
