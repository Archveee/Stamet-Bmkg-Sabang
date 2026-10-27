<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Rupadana\ApiService\Resources\TokenResource; // Import TokenResource secara eksplisit

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('app')
            ->login()

            // BRANDING & IDENTITAS STASIUN
            ->brandName('BMKGSABANG APP v2')

            // FONT & WARNA GLOBAL
            ->font('Inter')
            ->colors([
                'primary' => Color::Sky,
                'secondary' => Color::Slate,
                'danger' => Color::Rose,
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info' => Color::Blue,
            ])

            // PENGATURAN TEMA
            ->darkMode(true)

            // EFEK VISUAL NAVIGASI
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            ->maxContentWidth('full')

            // FITUR PERFORMA & KEAMANAN UI
            ->spa()
            ->unsavedChangesAlerts()
            ->broadcasting(true)

            // DISCOVER RESOURCES & PAKSA REGISTRASI TOKEN RESOURCE
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([
                TokenResource::class, // <--- Paksa Filament mendaftarkan TokenResource
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->profile()
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
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
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                \Rupadana\ApiService\ApiServicePlugin::make(),
                \TomatoPHP\FilamentUsers\FilamentUsersPlugin::make(),
                \Saade\FilamentLaravelLog\FilamentLaravelLogPlugin::make()
                    ->navigationGroup('System Settings')
                    ->authorize(
                        fn() => auth()->user()->isAdmin()
                    )
            ])
            ->navigationItems([
                // Paksa pembuatan menu API Tokens di grup Pelindung
                NavigationItem::make('API Tokens')
                    ->url(fn (): string => TokenResource::getUrl())
                    ->icon('heroicon-o-key')
                    ->group('Pelindung')
                    ->sort(10),

                NavigationItem::make('Panduan Operasional')
                    ->url('https://drive.google.com/file/d/1wxvlR6riyFGi9z6UpV4zaouQFL4LI_yA/view?usp=sharing')
                    ->icon('heroicon-o-book-open')
                    ->group('Bantuan')
                    ->openUrlInNewTab()
                    ->sort(11),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
