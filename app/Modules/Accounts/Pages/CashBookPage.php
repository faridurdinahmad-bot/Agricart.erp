<?php

namespace App\Modules\Accounts\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Accounts\Clusters\AccountsCluster;
use App\Modules\Accounts\Navigation\AccountsNavigation;
use Filament\Support\Icons\Heroicon;

class CashBookPage extends BaseModulePage
{
    protected static ?string $cluster = AccountsCluster::class;

    protected static ?string $navigationLabel = 'Cash Book';

    protected static ?string $title = 'Cash Book';

    protected static ?string $slug = 'cash-book';

    protected static ?int $navigationSort = AccountsNavigation::CASH_BOOK;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;
}
