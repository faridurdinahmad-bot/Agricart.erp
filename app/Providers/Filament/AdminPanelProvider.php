<?php

namespace App\Providers\Filament;

use App\Core\Filament\FontProviders\SystemFontProvider;
use App\Core\Filament\Pages\Dashboard;
use App\Core\Modules\ModuleRegistry;
use App\Filament\Auth\Login;
use App\Http\Middleware\RedirectRegistrationApplicants;
use Filament\Enums\GlobalSearchPosition;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->brandName('Agricart')
            ->font('Arial', provider: SystemFontProvider::class, preload: [])
            ->breadcrumbs()
            ->darkMode()
            ->defaultThemeMode(ThemeMode::Light)
            ->globalSearch(position: GlobalSearchPosition::Sidebar) // Rendered in topbar via navigation.blade.php hook
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('11.25rem')
            ->subNavigationPosition(SubNavigationPosition::Top)
            ->colors([
                'primary' => Color::hex('#83B735'),
                'warning' => Color::hex('#FBBC34'),
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->homeUrl(fn (): string => Dashboard::getUrl())
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                RedirectRegistrationApplicants::class,
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): View => view('filament.components.topbar.tools'),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
                fn (): View => view('filament.components.topbar.navigation'),
            )
            ->renderHook(
                PanelsRenderHook::CONTENT_BEFORE,
                fn (): View => view('filament.components.layout.module-sub-navigation'),
            );

        ModuleRegistry::configurePanel($panel);

        return $panel;
    }
}
