<div class="separator"></div>

<ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">

    <li class="nav-item">
        <a class="nav-link text-active-primary py-5 me-6 {{ request()->routeIs('admin.client_paid_invoices') ? 'active' : '' }}"
            href="{{ route('admin.client_paid_invoices', $all_data->id) }}">
            {{ trans('clients.invoices') }}
        </a>
    </li>

    @can('add_client_invoice')
        <li class="nav-item">
            <a class="nav-link text-active-primary py-5 me-6 {{ request()->routeIs('admin.client_invoices') ? 'active' : '' }}"
                href="{{ route('admin.client_invoices', $all_data->id) }}">
                {{ trans('clients.add_invoice') }}
            </a>
        </li>
    @endcan
</ul>
