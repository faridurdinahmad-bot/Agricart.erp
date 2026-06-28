<?php

namespace App\Modules\Accounts\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Accounts\Clusters\AccountsCluster;
use App\Modules\Accounts\Navigation\AccountsNavigation;
use Filament\Support\Icons\Heroicon;

class TransfersPage extends BaseModulePage
{
    protected static ?string $cluster = AccountsCluster::class;

    protected static ?string $navigationLabel = 'Transfers';

    protected static ?string $title = 'Transfers';

    protected static ?string $slug = 'transfers';

    protected static ?int $navigationSort = AccountsNavigation::TRANSFERS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;
}
