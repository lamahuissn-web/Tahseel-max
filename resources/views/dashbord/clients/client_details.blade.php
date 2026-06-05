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
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    .session-detail-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 12px;
        border-right: 4px solid #28a745;
        margin-bottom: 10px;
    }
    .session-detail-card.offline {
        border-right-color: #6c757d;
    }
    .session-detail-item {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        font-size: 13px;
    }
    .session-detail-label {
        color: #6c757d;
        font-weight: 500;
    }
    .session-detail-value {
        font-weight: 600;
        direction: ltr;
        text-align: left;
    }
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
    @include('dashbord.clients._client_content')
@endsection
