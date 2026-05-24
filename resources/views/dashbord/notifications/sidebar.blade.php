<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="" style="padding-top: 20px;padding-right: 20px">
        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class=" nav-icon fa fa-cog fa-fw text-primary"></i>

                    <?= trans('notifications.notifications') ?>

                </h3>
            </div>


            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-sm-12">
                        <nav class="mt-2" style="background-color: #fff4f0 !important; border-radius: 5px;">
                            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                                data-accordion="false">
                                @can('view_new_clients_notifications')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.new_clients_notifications') }}" class="nav-link @if (Route::is('admin.new_clients_notifications')) active @endif" style=" width: 100%;">
                                <span style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>
                                        <i class="far fa-circle nav-icon text-warning"></i>
                                        {{trans('notifications.new_clients')}}
                                    </span>
                                    <span class="badge badge-danger" style="order: 1; margin-left: 5px;">{{count_notifications_clients()}}</span>
                                </span>
                                        </a>
                                    </li>
                                    <hr class="nav-separator">
                                @endcan
                                @can('view_unpaid_invoices_notifications')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.unpaid_invoices_notifications') }}" class="nav-link @if (Route::is('admin.unpaid_invoices_notifications')) active @endif" style=" width: 100%;">
                                <span style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>
                                        <i class="far fa-circle nav-icon text-warning"></i>
                                        {{trans('settings.unpaid_invoice')}}
                                    </span>
                                    <span class="badge badge-danger" style="order: 1; margin-left: 5px;">{{count_invoice_reminder_notifications()}}</span>
                                </span>
                                        </a>
                                    </li>
                                    <hr class="nav-separator">
                                @endcan
                                    <li class="nav-item">
                                        <a href="{{ route('admin.invoice_management_notifications') }}" class="nav-link @if (Route::is('admin.invoice_management_notifications')) active @endif" style=" width: 100%;">
                                            <span style="display: flex; justify-content: space-between; align-items: center;">
                                                <span>
                                                    <i class="far fa-circle nav-icon text-info"></i>
                                                    {{trans('notifications.invoice_management')}}
                                                </span>
                                                <span class="badge badge-danger" style="order: 1; margin-left: 5px;">{{count_invoice_management_notifications()}}</span>
                                            </span>
                                        </a>
                                    </li>
                                    <hr class="nav-separator">

                                    <li class="nav-item">
                                        <a href="{{ route('admin.invoices_process_notifications') }}" class="nav-link @if (Route::is('admin.invoices_process_notifications')) active @endif" style=" width: 100%;">
                                <span style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>
                                        <i class="far fa-circle nav-icon text-warning"></i>
                                        {{trans('settings.invoices_process')}}
                                    </span>
                                    <span class="badge badge-danger" style="order: 1; margin-left: 5px;">{{count_invoices_process_notifications()}}</span>
                                </span>
                                        </a>
                                    </li>
                                    <hr class="nav-separator">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.transfers_notifications') }}" class="nav-link @if (Route::is('admin.transfers_notifications')) active @endif" style=" width: 100%;">
                                <span style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>
                                        <i class="far fa-circle nav-icon text-warning"></i>
                                        {{trans('settings.transfers')}}
                                    </span>
                                    <span class="badge badge-danger" style="order: 1; margin-left: 5px;">{{count_transfers_notifications()}}</span>
                                </span>
                                        </a>
                                    </li>
                                    <hr class="nav-separator">


                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
