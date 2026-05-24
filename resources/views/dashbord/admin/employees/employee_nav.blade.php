


<div class="separator"></div>

<ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
    @can('view_employee_files')
    <li class="nav-item">
        <a class="nav-link text-active-primary py-5 me-6 {{ request()->routeIs('admin.employee_files') ? 'active' : '' }}"
            href="{{ route('admin.employee_files', $all_data->id) }}">
            <?= trans('employees.employee_files') ?>
        </a>
    </li>

    @endcan
    @can('view_employee_masrofat')
    <li class="nav-item">
        <a class="nav-link text-active-primary py-5 me-6 {{ request()->routeIs('admin.employee_masrofat') ? 'active' : '' }}"
            href="{{ route('admin.employee_masrofat', $all_data->id) }}">
            {{ trans('employees.employee_masrofat') }}
        </a>
    </li>
    @endcan

    @can('view_employee_revenues')
    <li class="nav-item">
        <a class="nav-link text-active-primary py-5 me-6 {{ request()->routeIs('admin.employee_revenues') ? 'active' : '' }}"
            href="{{ route('admin.employee_revenues', $all_data->id) }}">
            {{ trans('employees.employee_revenues') }}
        </a>
    </li>
    @endcan

    @can('view_employee_transactions')
    <li class="nav-item">
        <a class="nav-link text-active-primary py-5 me-6 {{ request()->routeIs('admin.employee_transactions') ? 'active' : '' }}"
            href="{{ route('admin.employee_transactions', $all_data->id) }}">
            {{ trans('employees.employee_transactions') }}
        </a>
    </li>
    @endcan
</ul>