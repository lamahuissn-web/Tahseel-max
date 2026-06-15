@extends('dashbord.layouts.master')

@section('css')
<style>
    .online-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-left: 6px;
        animation: pulse 2s infinite;
    }
    .online-dot.online { background: #28a745; box-shadow: 0 0 6px #28a745; }
    .online-dot.offline { background: #6c757d; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    .session-detail-card {
        background: #f8f9fa; border-radius: 10px; padding: 12px;
        border-right: 4px solid #28a745; margin-bottom: 10px;
    }
    .session-detail-card.offline { border-right-color: #6c757d; }
    .session-detail-item { display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px; }
    .session-detail-label { color: #6c757d; font-weight: 500; }
    .session-detail-value { font-weight: 600; direction: ltr; text-align: left; }

    /* Tabs */
    .client-tabs .nav-link {
        font-weight: 600; font-size: 15px; border: none;
        padding: 12px 20px; color: #6c757d; border-bottom: 3px solid transparent;
    }
    .client-tabs .nav-link.active {
        color: #0d6efd; border-bottom-color: #0d6efd; background: transparent;
    }
    .client-tabs .nav-link i { margin-left: 6px; }
    #internetTabContent { min-height: 300px; }
</style>
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('clients.client_details') . ' - ' . $client->name;
    $breadcrumbs = [
        ['label' => trans('Toolbar.home'), 'link' => route('admin.clients.create')],
        ['label' => trans('Toolbar.clients'), 'link' => route('admin.clients.index')],
        ['label' => $client->name, 'link' => ''],
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
    {{-- Tabs Navigation --}}
    <ul class="nav nav-tabs client-tabs mb-4" id="clientTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoicesTabPane"
                    type="button" role="tab" aria-selected="true">
                <i class="bi bi-receipt"></i> {{ trans('clients.invoices_tab') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="internet-tab" data-bs-toggle="tab" data-bs-target="#internetTabPane"
                    type="button" role="tab" aria-selected="false" onclick="loadInternetTab({{ $client->id }})">
                <i class="bi bi-wifi"></i> 🌐 {{ trans('clients.internet_tab') }}
            </button>
        </li>
    </ul>

    {{-- Tabs Content --}}
    <div class="tab-content" id="clientTabsContent">
        {{-- Invoices Tab (existing content) --}}
        <div class="tab-pane fade" id="invoicesTabPane" role="tabpanel" aria-labelledby="invoices-tab">
            @include('dashbord.clients._client_content')
        </div>

        {{-- Internet Tab (loaded via AJAX) --}}
        <div class="tab-pane fade show active" id="internetTabPane" role="tabpanel" aria-labelledby="internet-tab">
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
    function loadInternetTab(clientId) {
        var container = document.getElementById('internetTabContent');
        if (!container || container.dataset.loaded === 'true') return;

        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        fetch('/' + getLocalePrefix() + '/admin/clients/' + clientId + '/internet-tab')
            .then(r => r.json())
            .then(res => {
                container.dataset.loaded = 'true';
                container.innerHTML = res.html;
            })
            .catch(() => {
                container.innerHTML = '<div class="alert alert-danger">Failed to load internet data</div>';
            });
    }
    </script>
@endsection
