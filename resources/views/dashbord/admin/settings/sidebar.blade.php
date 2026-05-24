<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="" style="padding-top: 20px;padding-right: 20px">
        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class=" nav-icon fa fa-cog fa-fw text-primary"></i>

                    <?= trans('settings.general_settings') ?>

                </h3>
            </div>


            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-sm-12">
                        <nav class="mt-2" style="background-color: #fff4f0 !important; border-radius: 5px;">
                            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                                data-accordion="false">
                                @can('view_subscriptions')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.subscriptions') }}"
                                            class="nav-link @if (Route::is('admin.subscriptions')) active @endif"
                                            style=" width: 100%;">
                                            <span
                                                style="display: flex; justify-content: space-between; align-items: center;">
                                                <span>
                                                    <i class="far fa-circle nav-icon text-warning"></i>
                                                    {{ trans('settings.subscriptions') }}
                                                </span>
                                                <span class="badge badge-danger"
                                                    style="order: 1; margin-left: 5px;">{{ count_subscriptions() }}</span>
                                            </span>
                                        </a>
                                    </li>
                                    <hr class="nav-separator">
                                @endcan
                                @can('view_sarf_band')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.sarf_bands') }}"
                                            class="nav-link @if (Route::is('admin.sarf_bands')) active @endif"
                                            style=" width: 100%;">
                                            <span
                                                style="display: flex; justify-content: space-between; align-items: center;">
                                                <span>
                                                    <i class="far fa-circle nav-icon text-warning"></i>
                                                    {{ trans('settings.sarf_band') }}
                                                </span>
                                                <span class="badge badge-danger"
                                                    style="order: 1; margin-left: 5px;">{{ count_sarf_band() }}</span>
                                            </span>
                                        </a>
                                    </li>
                                    <hr class="nav-separator">
                                @endcan
                                <li class="nav-item">
                                    <a href="{{ route('admin.app_config') }}"
                                        class="nav-link @if (Route::is('admin.app_config')) active @endif"
                                        style=" width: 100%;">
                                        <span
                                            style="display: flex; justify-content: space-between; align-items: center;">
                                            <span>
                                                <i class="far fa-circle nav-icon text-warning"></i>
                                                {{ trans('settings.app_config') }}
                                            </span>
                                            <span class="badge badge-danger"
                                                style="order: 1; margin-left: 5px;">0</span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
