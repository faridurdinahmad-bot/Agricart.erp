<?php

namespace App\Providers;

use App\Core\Modules\ModuleRegistry;
use App\Modules\Accounts\AccountsModule;
use App\Modules\Approvals\ApprovalsModule;
use App\Modules\Catalog\CatalogModule;
use App\Modules\Contacts\ContactsModule;
use App\Modules\Documentation\DocumentationModule;
use App\Modules\HR\HRModule;
use App\Modules\Inventory\InventoryModule;
use App\Modules\Logistics\LogisticsModule;
use App\Modules\Marketplace\MarketplaceModule;
use App\Modules\Reports\ReportsModule;
use App\Modules\Sales\SalesModule;
use App\Modules\Settings\SettingsModule;
use App\Modules\Store\StoreModule;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        ModuleRegistry::register(ApprovalsModule::class);
        ModuleRegistry::register(ContactsModule::class);
        ModuleRegistry::register(CatalogModule::class);
        ModuleRegistry::register(InventoryModule::class);
        ModuleRegistry::register(SalesModule::class);
        ModuleRegistry::register(StoreModule::class);
        ModuleRegistry::register(LogisticsModule::class);
        ModuleRegistry::register(ReportsModule::class);
        ModuleRegistry::register(HRModule::class);
        ModuleRegistry::register(AccountsModule::class);
        ModuleRegistry::register(MarketplaceModule::class);
        ModuleRegistry::register(SettingsModule::class);
        ModuleRegistry::register(DocumentationModule::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
