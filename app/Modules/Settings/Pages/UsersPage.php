<?php

namespace App\Modules\Settings\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Support\Icons\Heroicon;

class UsersPage extends BaseModulePage
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $title = 'Users';

    protected static ?string $slug = 'users';

    protected static ?int $navigationSort = SettingsNavigation::USERS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;
}
