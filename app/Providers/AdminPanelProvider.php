<?php

namespace App\Providers;

use App\Filament\Pages\OwnerLogin;
use App\Filament\Pages\Today;
use App\Filament\Pages\Analytics;
use App\Filament\Pages\Gmail;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('CEO Dashboard')
            ->login(OwnerLogin::class)
            ->strictAuthorization()
            ->databaseTransactions()
            ->globalSearch(false)
            ->colors(['primary' => Color::Amber])
            ->defaultThemeMode(ThemeMode::System)
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('4rem')
            ->sidebarWidth('15rem')
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.dashboard-head'))
            ->pages([Today::class, Analytics::class, Gmail::class])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class], isPersistent: true);
    }
}
