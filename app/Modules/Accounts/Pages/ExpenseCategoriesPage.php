<?php

namespace App\Modules\Accounts\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Accounts\Clusters\AccountsCluster;
use App\Modules\Accounts\Navigation\AccountsNavigation;
use Filament\Support\Icons\Heroicon;

class ExpenseCategoriesPage extends BaseModulePage
{
    protected static ?string $cluster = AccountsCluster::class;

    protected static ?string $navigationLabel = 'Expense Categories';

    protected static ?string $title = 'Expense Categories';

    protected static ?string $slug = 'expense-categories';

    protected static ?int $navigationSort = AccountsNavigation::EXPENSE_CATEGORIES;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
}
