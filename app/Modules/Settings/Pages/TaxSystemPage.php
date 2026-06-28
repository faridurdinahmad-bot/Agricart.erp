<?php

namespace App\Modules\Settings\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Support\Icons\Heroicon;

class TaxSystemPage extends BaseModulePage
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Tax';

    protected static ?string $title = 'Tax System';

    protected static ?string $slug = 'tax-system';

    protected static ?int $navigationSort = SettingsNavigation::TAX_SYSTEM;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;
}
