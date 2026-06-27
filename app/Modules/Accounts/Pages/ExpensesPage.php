<?php

namespace App\Modules\Accounts\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Accounts\Clusters\AccountsCluster;
use App\Modules\Accounts\Navigation\AccountsNavigation;
use Filament\Support\Icons\Heroicon;

class ExpensesPage extends BaseModulePage
{
    protected static ?string $cluster = AccountsCluster::class;

    protected static ?string $navigationLabel = 'Expenses';

    protected static ?string $title = 'Expenses';

    protected static ?string $slug = 'expenses';

    protected static ?int $navigationSort = AccountsNavigation::EXPENSES;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;
}
