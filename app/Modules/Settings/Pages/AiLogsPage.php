<?php

namespace App\Modules\Settings\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Support\Icons\Heroicon;

class AiLogsPage extends BaseModulePage
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Logs';

    protected static ?string $title = 'AI Logs';

    protected static ?string $slug = 'ai-logs';

    protected static ?int $navigationSort = SettingsNavigation::AI_LOGS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedDocumentText;
}
