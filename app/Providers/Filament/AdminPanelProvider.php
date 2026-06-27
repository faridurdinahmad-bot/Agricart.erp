<?php

namespace App\Providers\Filament;

use App\Core\Filament\FontProviders\SystemFontProvider;
use App\Core\Modules\ModuleRegistry;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
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
            ->login()
            ->brandName('Agricart')
            ->font('Arial', provider: SystemFontProvider::class, preload: [])
            ->breadcrumbs()
            ->darkMode()
            ->defaultThemeMode(ThemeMode::Light)
            ->globalSearch()
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
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
                FilamentInfoWidget::class,
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
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): View => view('filament.components.topbar.tools'),
            );

        ModuleRegistry::configurePanel($panel);

        return $panel;
    }
}
