<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
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
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<style>
                    /* 1. Global Reset & Body */
                    body {
                        background-color: #f8fafc !important; /* bg-slate-50 */
                    }

                    /* 2. Flat Cards / Sections (SaaS Minimalist) */
                    .fi-section {
                        background-color: #ffffff !important;
                        border: 1px solid #e2e8f0 !important; /* border-slate-200 */
                        box-shadow: none !important;
                        border-radius: 0.75rem !important; /* rounded-xl */
                    }
                    .fi-section-header {
                        border-bottom: 1px solid #f1f5f9 !important;
                        padding-bottom: 0.75rem !important;
                        margin-bottom: 1rem !important;
                    }

                    /* 3. Input Form Customization (bg-slate-50, soft borders) */
                    .fi-input-wrp {
                        background-color: #f8fafc !important; /* bg-slate-50 */
                        border: 1px solid #e2e8f0 !important; /* border-slate-200 */
                        box-shadow: none !important;
                        border-radius: 0.5rem !important; /* rounded-lg */
                        transition: all 0.2s ease-in-out !important;
                    }
                    .fi-input-wrp:focus-within {
                        background-color: #ffffff !important;
                        border-color: #4f46e5 !important; /* primary indigo */
                        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15) !important;
                    }
                    .fi-input-wrp input {
                        background-color: transparent !important;
                    }

                    /* 4. Danger Zone Section Custom Styling */
                    .danger-zone-section {
                        border: 1px solid #fca5a5 !important; /* border-red-300 */
                        background-color: #fffbfa !important; /* bg-red-50/10 */
                    }
                    .danger-zone-section .fi-section-header-title {
                        color: #dc2626 !important; /* text-red-600 */
                    }
                    .danger-zone-section .fi-section-header-description {
                        color: #7f1d1d !important; /* text-red-900 */
                    }

                    /* 5. Clean Tables & Borders */
                    .fi-ta-table {
                        background-color: #ffffff !important;
                        border-collapse: collapse !important;
                    }
                    .fi-ta-table th, .fi-ta-table td {
                        border-bottom: 1px solid #f1f5f9 !important;
                    }
                    .fi-ta-header, .fi-ta-content, .fi-wi, .fi-btn {
                        border-radius: 0.75rem !important;
                    }

                    /* 6. Clean Topbar & Sidebar Header Alignment */
                    .fi-topbar {
                        background-color: #ffffff !important;
                        border-bottom: 1px solid #f1f5f9 !important; /* border-slate-100 */
                        box-shadow: none !important;
                        height: 4rem !important;
                    }
                    .fi-sidebar {
                        background-color: #ffffff !important;
                        border-right: 1px solid #f1f5f9 !important;
                    }
                    .fi-sidebar-header {
                        border-bottom: 1px solid #f1f5f9 !important;
                        height: 4rem !important;
                        display: flex !important;
                        align-items: center !important;
                    }

                    /* 7. Boxed Icon Buttons (Bell, Theme Toggle, etc.) */
                    .fi-topbar .fi-icon-btn, 
                    .fi-theme-switcher {
                        border: 1px solid #e2e8f0 !important; /* border-slate-200 */
                        background-color: #ffffff !important;
                        border-radius: 0.5rem !important; /* rounded-lg */
                        padding: 0.25rem !important;
                        transition: all 0.15s ease-in-out !important;
                        box-shadow: none !important;
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                    }
                    .fi-topbar .fi-icon-btn:hover, 
                    .fi-theme-switcher:hover {
                        background-color: #f8fafc !important; /* bg-slate-50 */
                        border-color: #cbd5e1 !important;
                    }
                    .fi-theme-switcher button {
                        border-radius: 0.375rem !important;
                    }
                </style>',
            )
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn (): string => '<div class="w-full py-8 mt-16 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-400 gap-3">
                    <div>
                        &copy; ' . date('Y') . ' NetLabs. Hak Cipta Dilindungi.
                    </div>
                    <div class="flex gap-6">
                        <a href="#" class="hover:text-indigo-600 transition duration-200">Kebijakan Privasi</a>
                        <a href="#" class="hover:text-indigo-600 transition duration-200">Syarat & Ketentuan</a>
                    </div>
                </div>',
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // Widget default dinonaktifkan agar custom dashboard widget naik ke atas
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
