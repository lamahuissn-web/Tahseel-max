<?php

use Illuminate\Support\Facades\Route; ?>

<style>
    .sidebar-menu-icon {
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-inline-end: 10px;
        flex-shrink: 0;
    }

    .sidebar-menu-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .sidebar-menu-link:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .sidebar-menu-link.active {
        background-color: rgba(13, 110, 253, 0.1);
        border-inline-start: 3px solid #0d6efd;
    }

    .sidebar-section-divider {
        margin: 1rem 0;
        height: 1px;
        background: linear-gradient(to right, transparent, rgba(25, 135, 84, 0.3), transparent);
        border: none;
    }

    .sidebar-section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sidebar-section-icon {
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #198754;
    }

    .sidebar-notification-badge {
        margin-inline-start: auto;
        margin-inline-end: 5px;
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.4;
        }
    }

    .blinking {
        animation: blink 1.5s infinite;
    }

    .menu-sub-item {
        padding-inline-start: 2.5rem;
    }

    .menu-sub-link {
        display: flex;
        align-items: center;
        padding: 0.6rem 1rem;
        transition: background-color 0.3s ease, padding-inline-start 0.3s ease;
        white-space: nowrap;
        min-width: 0;
    }

    .menu-sub-link:hover {
        background-color: rgba(0, 0, 0, 0.03);
        padding-inline-start: 1.25rem;
    }

    .menu-sub-link.active {
        background-color: rgba(13, 110, 253, 0.08);
        border-inline-start: 2px solid #0d6efd;
    }

    .menu-sub-link .menu-title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
        min-width: 0;
    }
</style>

<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center">
            <img alt="Logo"
                src="{{ asset(!empty($mainData->image) ? $mainData->image : 'assets/media/logos/default-dark.svg') }}"
                class="h-50px app-sidebar-logo-default" />
        </a>
        <div id="kt_app_sidebar_toggle" class="app-sidebar-toggle btn btn-icon btn-sm h-30px w-30px rotate"
            data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
            data-kt-toggle-name="app-sidebar-minimize">
            <span class="svg-icon svg-icon-2 rotate-180">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.5"
                        d="M14.2657 11.4343L18.45 7.25C18.8642 6.83579 18.8642 6.16421 18.45 5.75C18.0358 5.33579 17.3642 5.33579 16.95 5.75L11.4071 11.2929C11.0166 11.6834 11.0166 12.3166 11.4071 12.7071L16.95 18.25C17.3642 18.6642 18.0358 18.6642 18.45 18.25C18.8642 17.8358 18.8642 17.1642 18.45 16.75L14.2657 12.5657C13.9533 12.2533 13.9533 11.7467 14.2657 11.4343Z"
                        fill="currentColor" />
                    <path
                        d="M8.2657 11.4343L12.45 7.25C12.8642 6.83579 12.8642 6.16421 12.45 5.75C12.0358 5.33579 11.3642 5.33579 10.95 5.75L5.40712 11.2929C5.01659 11.6834 5.01659 12.3166 5.40712 12.7071L10.95 18.25C11.3642 18.6642 12.0358 18.6642 12.45 18.25C12.8642 17.8358 12.8642 17.1642 12.45 16.75L8.2657 12.5657C7.95328 12.2533 7.95328 11.7467 8.2657 11.4343Z"
                        fill="currentColor" />
                </svg>
            </span>
        </div>
    </div>
    <!--end::Logo-->

    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5"
            data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px">

            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold px-3" id="#kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">



                {{-- Settings Management Section --}}
                <div class="menu-item">
                    <div class="menu-content sidebar-section-title" style="padding: 0.75rem 1rem 0.5rem; margin-top: 0.5rem;">
                        <span class="sidebar-section-icon">
                            <i class="bi bi-gear-fill text-success fs-3"></i>
                        </span>
                        <span class="fw-bold text-uppercase fs-5 text-success">
                            {{ trans('sidebar.settings_management') }}
                        </span>
                    </div>
                </div>

                {{-- Dashboard --}}
                @can('view_dashboard')
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.dashboard']) ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-speedometer2 text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.dashboard') }}</span>
                    </a>
                </div>
                @endcan

                @php
                $defaultSettingsLink = null;
                if (auth()->user()->can('view_subscriptions')) {
                $defaultSettingsLink = route('admin.subscriptions');
                } elseif (auth()->user()->can('view_sarf_band')) {
                $defaultSettingsLink = route('admin.sarf_bands');
                }
                @endphp
                @canany('view_subscriptions', 'view_sarf_band')
                @if ($defaultSettingsLink)
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs('admin.subscriptions') || request()->routeIs('admin.sarf_bands') ? 'active' : '' }}"
                        href="{{ $defaultSettingsLink }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-sliders text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.general_settings') }}</span>
                    </a>
                </div>
                @endif
                @endcanany

                {{-- App Config Settings --}}
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs('admin.app_config') ? 'active' : '' }}"
                        href="{{ route('admin.app_config') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-gear-wide-connected text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">إعدادات التطبيق</span>
                    </a>
                </div>

                {{-- WhatsApp Settings --}}
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs('admin.settings.whatsapp') ? 'active' : '' }}"
                        href="{{ route('admin.settings.whatsapp') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-whatsapp text-success fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('clients.whatsapp_settings') }}</span>
                    </a>
                </div>

                {{-- User & Employees Management Section --}}
                @canany(['list_roles', 'list_users', 'view_employees'])
                <hr class="sidebar-section-divider">

                <div class="menu-item">
                    <div class="menu-content sidebar-section-title" style="padding: 0.75rem 1rem 0.5rem; margin-top: 0.5rem;">
                        <span class="sidebar-section-icon">
                            <i class="bi bi-people-fill fs-3 text-success"></i>
                        </span>
                        <span class="fw-bold text-uppercase fs-6 text-success">
                            {{ trans('sidebar.user&employees_management') }}
                        </span>
                    </div>
                </div>
                @endcanany

                @can('view_employees')
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.employee_data', 'jobs', 'admin.archive_shelf_settings', 'shelf', 'admin.archive_settings', 'desk']) ? 'active' : '' }}"
                        href="{{ route('admin.employee_data') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-person-lines-fill text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.employee_data') }}</span>
                    </a>
                </div>
                @endcan

                @can('list_roles')
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.roles.index']) ? 'active' : '' }}"
                        href="{{ route('admin.roles.index') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-shield-lock text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.roles') }}</span>
                    </a>
                </div>
                @endcan

                @can('list_users')
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.users.index']) ? 'active' : '' }}"
                        href="{{ route('admin.users.index') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-clipboard-check text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.users') }}</span>
                    </a>
                </div>
                @endcan

                {{-- Clients Management Section --}}
                @can('list_clients')
                <hr class="sidebar-section-divider">

                <div class="menu-item">
                    <div class="menu-content sidebar-section-title" style="padding: 0.75rem 1rem 0.5rem; margin-top: 0.5rem;">
                        <span class="sidebar-section-icon">
                            <i class="bi bi-building fs-3 text-success"></i>
                        </span>
                        <span class="fw-bold text-uppercase fs-5 text-success">
                            {{ trans('sidebar.clients_management') }}
                        </span>
                    </div>
                </div>

                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.clients.index']) ? 'active' : '' }}"
                        href="{{ route('admin.clients.index') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-people text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.clients') }}</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.clients.import.show']) ? 'active' : '' }}"
                        href="{{ route('admin.clients.import.show') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-file-earmark-arrow-up text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.import_clients') }}</span>
                    </a>
                </div>
                @endcan

                {{-- Invoices & Reports Management Section --}}
                @canany(['list_invoices', 'view_reports'])
                <hr class="sidebar-section-divider">

                <div class="menu-item">
                    <div class="menu-content sidebar-section-title" style="padding: 0.75rem 1rem 0.5rem; margin-top: 0.5rem;">
                        <span class="sidebar-section-icon">
                            <i class="bi bi-file-text fs-3 text-success"></i>
                        </span>
                        <span class="fw-bold text-uppercase fs-5 text-success">
                            {{ trans('sidebar.invoices&reports_management') }}
                        </span>
                    </div>
                </div>
                @endcanany

                @can('list_invoices')
                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['admin.invoices.index', 'admin.due_monthly_invoices', 'admin.new_paid_invoices']) ? 'show' : '' }}">
                    <span class="menu-link sidebar-menu-link">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-file-earmark-text text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.invoices') }}</span>
                        <span class="menu-arrow"></span>
                    </span>

                    <div class="menu-sub menu-sub-accordion {{ request()->routeIs(['admin.invoices.index', 'admin.due_monthly_invoices', 'admin.new_paid_invoices']) ? 'show' : '' }}">
                        <div class="menu-item menu-sub-item">
                            <a class="menu-link menu-sub-link {{ request()->routeIs('admin.invoices.index') ? 'active' : '' }}"
                                href="{{ route('admin.invoices.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sidebar.all_invoices') }}</span>
                            </a>
                        </div>

                        <div class="menu-item menu-sub-item">
                            <a class="menu-link menu-sub-link {{ request()->routeIs('admin.due_monthly_invoices') ? 'active' : '' }}"
                                href="{{ route('admin.due_monthly_invoices') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sidebar.due_monthly_invoices') }}</span>
                            </a>
                        </div>

                        <div class="menu-item menu-sub-item">
                            <a class="menu-link menu-sub-link {{ request()->routeIs('admin.new_paid_invoices') ? 'active' : '' }}"
                                href="{{ route('admin.new_paid_invoices') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sidebar.new_paid_invoices') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
                @endcan

                @can('view_reports')
                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->routeIs(['admin.reports.reports', 'admin.reports.daily_by_users', 'admin.reports.monthly_by_users', 'admin.reports.comprehensive_by_users', 'admin.reports.overdue_invoices', 'admin.reports.profit_and_loss', 'admin.reports.expenses_by_bands_and_months', 'admin.reports.revenues_by_users_and_months', 'admin.reports.clients_remaining']) ? 'show' : '' }}">
                    <span class="menu-link sidebar-menu-link">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-graph-up-arrow text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.reports') }}</span>
                        <span class="menu-arrow"></span>
                    </span>

                    <div class="menu-sub menu-sub-accordion {{ request()->routeIs(['admin.reports.reports', 'admin.reports.daily_by_users', 'admin.reports.monthly_by_users', 'admin.reports.comprehensive_by_users', 'admin.reports.overdue_invoices', 'admin.reports.profit_and_loss', 'admin.reports.expenses_by_bands_and_months', 'admin.reports.revenues_by_users_and_months', 'admin.reports.clients_remaining']) ? 'show' : '' }}">
                        <div class="menu-item menu-sub-item">
                            <a class="menu-link menu-sub-link {{ request()->routeIs('admin.reports.reports') ? 'active' : '' }}"
                                href="{{ route('admin.reports.reports') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sidebar.all_reports') ?? 'التقارير' }}</span>
                            </a>
                        </div>

                        <div class="menu-item menu-sub-item">
                            <a class="menu-link menu-sub-link {{ request()->routeIs('admin.reports.daily_by_users') ? 'active' : '' }}"
                                href="{{ route('admin.reports.daily_by_users') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sidebar.daily_report_by_users') ?? 'تقرير يومي بالمستخدمين' }}</span>
                            </a>
                        </div>

                        <div class="menu-item menu-sub-item">
                            <a class="menu-link menu-sub-link {{ request()->routeIs('admin.reports.monthly_by_users') ? 'active' : '' }}"
                                href="{{ route('admin.reports.monthly_by_users') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sidebar.monthly_report_by_users') ?? 'تقرير شهري بالمستخدمين' }}</span>
                            </a>
                        </div>

                        <div class="menu-item menu-sub-item">
                            <a class="menu-link menu-sub-link {{ request()->routeIs('admin.reports.comprehensive_by_users') ? 'active' : '' }}"
                                href="{{ route('admin.reports.comprehensive_by_users') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sidebar.comprehensive_report_by_users') ?? 'تقرير شامل بالمستخدمين' }}</span>
                            </a>
                        </div>

                        <div class="menu-item menu-sub-item">
                            <a class="menu-link menu-sub-link {{ request()->routeIs('admin.reports.overdue_invoices') ? 'active' : '' }}"
                                href="{{ route('admin.reports.overdue_invoices') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sidebar.overdue_invoices') ?? 'الفواتير المتأخرة' }}</span>
                            </a>
                        </div>

                        <div class="menu-item menu-sub-item">
                            <a class="menu-link menu-sub-link {{ request()->routeIs('admin.reports.clients_remaining') ? 'active' : '' }}"
                                href="{{ route('admin.reports.clients_remaining') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title ">
                                    {{ trans('sidebar.clients_remaining') ?? 'المبالغ المتبقية' }}
                                    <span class="badge bg-danger" style="font-size: 0.65rem;">new</span>
                                </span>
                            </a>
                        </div>

                        <div class="menu-item menu-sub-item">
                            <a class="menu-link menu-sub-link {{ request()->routeIs('admin.reports.profit_and_loss') ? 'active' : '' }}"
                                href="{{ route('admin.reports.profit_and_loss') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sidebar.profit_and_loss') ?? 'تقرير الربح والخسارة' }}</span>
                            </a>
                        </div>

                        <div class="menu-item menu-sub-item">
                            <a class="menu-link menu-sub-link {{ request()->routeIs('admin.reports.expenses_by_bands_and_months') ? 'active' : '' }}"
                                href="{{ route('admin.reports.expenses_by_bands_and_months') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sidebar.expenses_by_bands_and_months') ?? 'المصروفات  التفصيلي' }}</span>
                            </a>
                        </div>

                        <div class="menu-item menu-sub-item">
                            <a class="menu-link menu-sub-link {{ request()->routeIs('admin.reports.revenues_by_users_and_months') ? 'active' : '' }}"
                                href="{{ route('admin.reports.revenues_by_users_and_months') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sidebar.revenues_by_users_and_months') ?? 'الإيرادات  التفصيلي' }}</span>
                            </a>
                        </div>
                    </div>
                </div>
                @endcan

                {{-- Finance Management Section --}}
                @canany(['list_eradat', 'list_masrofat', 'view_accounts', 'view_account_settings', 'view_financial_transactions', 'view_account_transfers'])
                <hr class="sidebar-section-divider">

                <div class="menu-item">
                    <div class="menu-content sidebar-section-title" style="padding: 0.75rem 1rem 0.5rem; margin-top: 0.5rem;">
                        <span class="sidebar-section-icon">
                            <i class="bi bi-currency-dollar fs-3 text-success"></i>
                        </span>
                        <span class="fw-bold text-uppercase fs-5 text-success">
                            {{ trans('sidebar.finance_management') }}
                        </span>
                    </div>
                </div>
                @endcanany

                @can('view_accounts')
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.accounts']) ? 'active' : '' }}"
                        href="{{ route('admin.accounts') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-wallet text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.accounts') }}</span>
                    </a>
                </div>
                @endcan

                @can('view_account_settings')
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.account_settings']) ? 'active' : '' }}"
                        href="{{ route('admin.account_settings') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-gear text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.account_settings') }}</span>
                    </a>
                </div>
                @endcan

                @can('view_financial_transactions')
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.financial_transactions.index']) ? 'active' : '' }}"
                        href="{{ route('admin.financial_transactions.index') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-cash-coin text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.financial_transactions') }}</span>
                    </a>
                </div>
                @endcan

                @can('view_account_transfers')
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.account_transfers']) ? 'active' : '' }}"
                        href="{{ route('admin.account_transfers') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-arrow-left-right text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.account_transfers') }}</span>
                    </a>
                </div>
                @endcan

                @can('list_eradat')
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.revenues.index']) ? 'active' : '' }}"
                        href="{{ route('admin.revenues.index') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-arrow-down-circle text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.revenues') }}</span>
                    </a>
                </div>
                @endcan

                @can('list_masrofat')
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.masrofat.index']) ? 'active' : '' }}"
                        href="{{ route('admin.masrofat.index') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-arrow-up-circle text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.masrofat') }}</span>
                    </a>
                </div>
                @endcan

                {{-- Notifications Management Section --}}
                @canany(['view_new_clients_notifications', 'view_unpaid_invoices_notifications'])
                <hr class="sidebar-section-divider">

                <div class="menu-item">
                    <div class="menu-content sidebar-section-title" style="padding: 0.75rem 1rem 0.5rem; margin-top: 0.5rem;">
                        <span class="sidebar-section-icon">
                            <i class="bi bi-bell-fill fs-3 text-success"></i>
                        </span>
                        <span class="fw-bold text-uppercase fs-5 text-success">
                            {{ trans('sidebar.notifications_management') }}
                        </span>
                    </div>
                </div>
                @endcanany

                @php
                $defaultNotificationLink = null;
                if (auth()->user()->can('view_new_clients_notifications')) {
                $defaultNotificationLink = route('admin.new_clients_notifications');
                } elseif (auth()->user()->can('view_unpaid_invoices_notifications')) {
                $defaultNotificationLink = route('admin.unpaid_invoices_notifications');
                } else {
                $defaultNotificationLink = route('admin.invoices_notifications');
                }
                @endphp

                @if ($defaultNotificationLink)
                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs('admin.new_clients_notifications') || request()->routeIs('admin.unpaid_invoices_notifications') || request()->routeIs('admin.invoices_notifications') || request()->routeIs('admin.transfers_notifications') ? 'active' : '' }}"
                        href="{{ $defaultNotificationLink }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-bell text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.notifications') }}</span>
                        @if (count_all_notifications_clients() > 0)
                        <span class="badge bg-danger blinking sidebar-notification-badge">
                            {{ count_all_notifications_clients() }}
                        </span>
                        @endif
                    </a>
                </div>
                @endif

           

                <div class="menu-item">
                    <a class="menu-link sidebar-menu-link {{ request()->routeIs(['admin.logs.index']) ? 'active' : '' }}"
                        href="{{ route('admin.logs.index') }}">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-activity text-muted fs-4"></i>
                        </span>
                        <span class="menu-title">{{ trans('sidebar.logs') }}</span>
                    </a>
                </div>

            </div>
        </div>
    </div>
    <!--end::sidebar menu-->

</div>