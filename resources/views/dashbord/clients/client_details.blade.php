@extends('dashbord.layouts.master')

@section('css')
<style>
.online-dot{display:inline-block;width:10px;height:10px;border-radius:50%;margin-left:6px;animation:pulse 2s infinite}
.online-dot.online{background:#28a745;box-shadow:0 0 6px #28a745}
.online-dot.offline{background:#6c757d}
@keyframes pulse{0%{opacity:1}50%{opacity:0.5}100%{opacity:1}}
#internetTabContent{min-height:300px}
</style>
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans("clients.client_details") . " - " . $client->name;
    $breadcrumbs = [
        ["label" => trans("Toolbar.home"), "link" => route("admin.clients.create")],
        ["label" => trans("Toolbar.clients"), "link" => route("admin.clients.index")],
        ["label" => $client->name, "link" => ""],
    ];
    PageTitle($title, $breadcrumbs);
    @endphp
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-right"></i> {{ trans('invoices.back') }}
        </a>
        @can('update_client')
        <a href="{{ route('admin.clients.edit', $client->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil-square"></i> {{ trans('clients.edit_clients') }}
        </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<!-- Client Basic Info Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <small class="text-muted d-block">رقم الهاتف</small>
                <span class="fw-bold">{{ $client->phone ?? '—' }}</span>
            </div>
            <div class="col-md-3 col-6">
                <small class="text-muted d-block">نوع الزبون</small>
                <span class="badge bg-info">{{ $client->client_type ?? '—' }}</span>
            </div>
            <div class="col-md-3 col-6">
                <small class="text-muted d-block">RADIUS (SAS)</small>
                <span class="fw-bold">{{ $client->sas_username ?? '—' }}</span>
            </div>
            <div class="col-md-3 col-6">
                <small class="text-muted d-block">الحالة</small>
                @if($client->is_active)
                    <span class="badge bg-success">🟢 نشط</span>
                @else
                    <span class="badge bg-secondary">🔴 غير نشط</span>
                @endif
            </div>
            <div class="col-md-3 col-6">
                <small class="text-muted d-block">الباقة</small>
                <span class="fw-bold">{{ $client->subscription->name ?? '—' }}</span>
            </div>
            <div class="col-md-3 col-6">
                <small class="text-muted d-block">السعر</small>
                <span class="fw-bold">\${{ number_format($client->price, 2) }}</span>
            </div>
            <div class="col-md-3 col-6">
                <small class="text-muted d-block">المبلغ المتبقي</small>
                <span class="fw-bold {{ ($client->remaining_amount_total ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                    \${{ number_format($client->remaining_amount_total ?? 0, 2) }}
                </span>
            </div>
            <div class="col-md-3 col-6">
                <small class="text-muted d-block">تاريخ البداية</small>
                <span class="fw-bold">{{ $client->start_date ?? ($client->subscription_date ?? '—') }}</span>
            </div>
            <div class="col-12">
                <small class="text-muted d-block">العنوان</small>
                <span>{{ $client->address1 ?? '—' }}{{ $client->address2 ? ' - ' . $client->address2 : '' }}</span>
            </div>
            @if($client->notes)
            <div class="col-12">
                <small class="text-muted d-block">ملاحظات</small>
                <span class="text-muted">{{ $client->notes }}</span>
            </div>
            @endif
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="clientTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoicesTabPane"
                type="button" role="tab">
            <i class="bi bi-receipt"></i> {{ trans('clients.invoices_tab') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="internet-tab" data-bs-toggle="tab" data-bs-target="#internetTabPane"
                type="button" role="tab">
            <i class="bi bi-wifi"></i> 🌐 {{ trans('clients.internet_tab') }}
        </button>
    </li>
</ul>

<div class="tab-content" id="clientTabsContent">
    <div class="tab-pane fade" id="invoicesTabPane" role="tabpanel">
        @include('dashbord.clients._client_content')
    </div>
    <div class="tab-pane fade show active" id="internetTabPane" role="tabpanel">
        <div id="internetTabContent" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading internet data...</p>
        </div>
    </div>
</div>
@endsection

@section('js')
    @include('dashbord.clients._radius_actions_js')
    <script>
    function getLocalePrefix() {
        var path = window.location.pathname;
        var match = path.match(/^\/(en|ar)\//);
        return match ? match[1] : 'en';
    }

    function loadInternetTab(clientId) {
        var container = document.getElementById('internetTabContent');
        if (!container) return;

        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        fetch('/' + getLocalePrefix() + '/admin/clients/' + clientId + '/internet-tab', {headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
            .then(function(r) { return r.json(); })
            .then(function(res) {
                container.innerHTML = res.html;
            })
            .catch(function(e) {
                container.innerHTML = '<div class="alert alert-danger">Failed to load: ' + e.message + '</div>';
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadInternetTab({{ $client->id }});
    });
    </script>
@endsection