<?php

namespace App\Modules\Settings\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Support\Icons\Heroicon;

class AiSettingsPage extends BaseModulePage
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'AI';

    protected static ?string $title = 'AI Settings';

    protected static ?string $slug = 'ai-settings';

    protected static ?int $navigationSort = SettingsNavigation::AI_SETTINGS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedSparkles;
}
