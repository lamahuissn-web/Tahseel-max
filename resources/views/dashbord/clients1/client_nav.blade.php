<div class="col-md-12">
    <div class="card" style="margin-right: 30px;margin-left: 30px; margin-top:-60px">
        <div class="card-body" style="padding: 10px">
            <ul class="nav nav-pills nav-pills-custom mb-3">

                {{-- @can('view_client_paid_invoices') --}}
                    <li class="nav-item mb-3 me-3 me-lg-6" st>

                        <a href="{{ route('admin.client_paid_invoices', $all_data->id) }}" style="background-color: linen;"
                            class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-success
                        flex-column overflow-hidden w-80px h-85px pt-5 pb-2 {{ request()->routeIs('admin.client_paid_invoices') ? 'active' : '' }}">

                            <div class="nav-icon mb-3">
                                <i class="bi bi-cash-stack fs-1 p-0"></i>
                            </div>

                            <span
                                class="nav-text text-gray-800 fw-bold fs-6 lh-1">{{ trans('clients.client_invoices') }}</span>

                            <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>

                        </a>

                    </li>
                {{-- @endcan --}}
                {{-- @can('view_client_unpaid_invoices')
                    <li class="nav-item mb-3 me-3 me-lg-6">

                        <a href="{{ route('admin.client_unpaid_invoices', $all_data->id) }}"
                            style="background-color: powderblue;"
                            class="nav-link btn btn-outline btn-flex btn-color-muted
                        btn-active-color-danger flex-column overflow-hidden w-80px h-85px
                        pt-5 pb-2 {{ request()->routeIs('admin.client_unpaid_invoices') ? 'active' : '' }}">
                            <div class="nav-icon mb-3">
                                <i class="bi bi-hourglass-split fs-1 p-0"></i>
                            </div>
                            <span
                                class="nav-text text-gray-800 fw-bold fs-6 lh-1">{{ trans('clients.unpaid_invoices') }}</span>
                            <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>

                        </a>

                    </li>
                @endcan --}}
                @can('add_client_invoice')
                    <li class="nav-item mb-3 me-3 me-lg-6">
                        <a href="{{ route('admin.client_invoices', $all_data->id) }}" style="background-color: powderblue;"
                            class="nav-link btn btn-outline btn-flex btn-color-muted
                                btn-active-color-success flex-column overflow-hidden w-80px h-85px
                                pt-5 pb-2 {{ request()->routeIs('admin.client_invoices') ? 'active' : '' }}">
                            <div class="nav-icon mb-3">
                                <i class="bi bi-plus-circle fs-1 p-0"></i>
                            </div>
                            <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">{{ trans('clients.add_invoice') }}</span>
                            <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-success"></span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </div>
</div>
