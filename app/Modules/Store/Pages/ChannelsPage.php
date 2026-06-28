<?php

namespace App\Modules\Store\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Store\Clusters\StoreCluster;
use App\Modules\Store\Navigation\StoreNavigation;
use Filament\Support\Icons\Heroicon;

class ChannelsPage extends BaseModulePage
{
    protected static ?string $cluster = StoreCluster::class;

    protected static ?string $navigationLabel = 'Channels';

    protected static ?string $title = 'Channels';

    protected static ?string $slug = 'channels';

    protected static ?int $navigationSort = StoreNavigation::CHANNELS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;
}
