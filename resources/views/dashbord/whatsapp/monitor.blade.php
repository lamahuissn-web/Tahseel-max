@extends('dashbord.layouts.master')

@section('title')
{{ trans('clients.whatsapp_control_center') }}
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('clients.whatsapp_control_center');
    $breadcrumbs = [
        ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
        ['label' => trans('clients.whatsapp_control_center'), 'link' => route('admin.whatsapp.dashboard')],
        ['label' => 'Connection Monitor', 'link' => ''],
    ];
    PageTitle($title, $breadcrumbs);
    @endphp
</div>
@endsection

@section('content')
@include('dashbord.whatsapp._partials.tab-nav')
<div id="kt_app_content_container" class="app-container container-xxxl">
    @if($emergencyStop == '1')
    <div class="alert alert-danger d-flex align-items-center p-5 mb-8">
        <i class="bi bi-exclamation-triangle-fill fs-2x me-4 text-white"></i>
        <div class="d-flex flex-column">
            <h4 class="mb-1 text-white">🚨 {{ trans('clients.whatsapp_emergency_active') ?? 'حالة طوارئ مفعلة' }}</h4>
            <span class="text-white opacity-75">{{ trans('clients.whatsapp_emergency_active_desc') ?? 'خدمة الواتساب موقوفة بواسطة زر الطوارئ' }}</span>
        </div>
    </div>
    @endif

    <div class="row g-5 g-xl-8 mb-8">
        <div class="col-12">
            @include('dashbord.whatsapp._partials.connection-monitor-panel')
        </div>
    </div>
</div>

@include('dashbord.whatsapp._partials.connection-monitor-scripts')
@endsection