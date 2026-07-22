@extends('dashbord.layouts.master')

@section('title')
WhatsApp Safety
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
        $title = 'WhatsApp Safety';
        $breadcrumbs = [
            ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
            ['label' => trans('clients.whatsapp_control_center'), 'link' => route('admin.whatsapp.dashboard')],
            ['label' => 'Safety', 'link' => ''],
        ];
        PageTitle($title, $breadcrumbs);
    @endphp
</div>
@endsection

@section('content')
@include('dashbord.whatsapp._partials.tab-nav')

@php
    $riskLevel = $rateLimit['risk_level'] ?? 'safe';
    $riskMap = [
        'safe' => ['class' => 'success', 'label' => 'Safe', 'icon' => 'bi-shield-check', 'text' => 'Sending is inside the configured safety limits.'],
        'warning' => ['class' => 'warning', 'label' => 'Warning', 'icon' => 'bi-exclamation-triangle', 'text' => 'Sending is near one of the configured limits. Slow down or let the queue continue later.'],
        'paused' => ['class' => 'danger', 'label' => 'Paused', 'icon' => 'bi-pause-circle', 'text' => $rateLimit['reason'] ?? 'Sending is paused by the safety limiter.'],
        'disabled' => ['class' => 'secondary', 'label' => 'Disabled', 'icon' => 'bi-shield-x', 'text' => 'Rate limiter is disabled. This is not recommended for OpenWA.'],
    ];
    $risk = $riskMap[$riskLevel] ?? $riskMap['safe'];
    $settings = $rateLimit['settings'] ?? [];
@endphp

<div id="kt_app_content_container" class="app-container container-xxxl">
    <div class="alert alert-{{ $risk['class'] }} d-flex align-items-center p-5 mb-8">
        <i class="bi {{ $risk['icon'] }} fs-2x me-4"></i>
        <div class="d-flex flex-column">
            <h4 class="mb-1">🛡️ WhatsApp Safety: {{ $risk['label'] }}</h4>
            <span>{{ $risk['text'] }}</span>
        </div>
        <div class="ms-auto text-end d-none d-md-block">
            <span class="badge badge-light fs-7">Checked: {{ optional($rateLimit['checked_at'] ?? null)->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s') }}</span>
        </div>
    </div>

    <div class="row g-5 g-xl-8 mb-8">
        <div class="col-xl-3 col-md-6">
            <div class="card card-xl-stretch">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="symbol symbol-45px me-4"><span class="symbol-label bg-primary-light"><i class="bi bi-clock-history fs-2x text-primary"></i></span></span>
                        <div>
                            <div class="fw-bold text-gray-800">Last Hour</div>
                            <div class="text-muted fs-7">Hourly cap usage</div>
                        </div>
                    </div>
                    <div class="fs-2x fw-bold text-gray-900">{{ $rateLimit['hourly_sent'] ?? 0 }} / {{ $settings['hourly_limit'] ?? 60 }}</div>
                    <div class="progress h-8px mt-3">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $rateLimit['hourly_percent'] ?? 0 }}%"></div>
                    </div>
                    <div class="text-muted fs-8 mt-2">{{ $rateLimit['hourly_percent'] ?? 0 }}% used</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-xl-stretch">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="symbol symbol-45px me-4"><span class="symbol-label bg-info-light"><i class="bi bi-calendar-day fs-2x text-info"></i></span></span>
                        <div>
                            <div class="fw-bold text-gray-800">Today</div>
                            <div class="text-muted fs-7">Daily cap usage</div>
                        </div>
                    </div>
                    <div class="fs-2x fw-bold text-gray-900">{{ $rateLimit['daily_sent'] ?? 0 }} / {{ $settings['daily_limit'] ?? 300 }}</div>
                    <div class="progress h-8px mt-3">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $rateLimit['daily_percent'] ?? 0 }}%"></div>
                    </div>
                    <div class="text-muted fs-8 mt-2">{{ $rateLimit['daily_percent'] ?? 0 }}% used</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-xl-stretch">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="symbol symbol-45px me-4"><span class="symbol-label bg-success-light"><i class="bi bi-hourglass-split fs-2x text-success"></i></span></span>
                        <div>
                            <div class="fw-bold text-gray-800">Delay Range</div>
                            <div class="text-muted fs-7">Randomized anti-pattern timing</div>
                        </div>
                    </div>
                    <div class="fs-2x fw-bold text-gray-900">{{ $rateLimit['delay_min_seconds'] ?? 0 }}–{{ $rateLimit['delay_max_seconds'] ?? 0 }}s</div>
                    <div class="text-muted fs-8 mt-2">Base {{ $settings['base_delay'] ?? 10 }}s, jitter ±{{ $settings['jitter_percent'] ?? 40 }}%</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-xl-stretch">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="symbol symbol-45px me-4"><span class="symbol-label bg-warning-light"><i class="bi bi-list-task fs-2x text-warning"></i></span></span>
                        <div>
                            <div class="fw-bold text-gray-800">Queue</div>
                            <div class="text-muted fs-7">Messages waiting / sending</div>
                        </div>
                    </div>
                    <div class="fs-2x fw-bold text-gray-900">{{ $pendingQueueCount }} / {{ $sendingQueueCount }}</div>
                    <div class="text-muted fs-8 mt-2">pending / sending</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-8">
        <div class="col-xl-6">
            <div class="card card-xl-stretch">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Safety Rules</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Current limiter configuration</span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gy-4">
                            <tbody>
                                <tr><td class="fw-semibold text-gray-700">Limiter enabled</td><td class="text-end"><span class="badge badge-{{ ($rateLimit['enabled'] ?? false) ? 'success' : 'danger' }}">{{ ($rateLimit['enabled'] ?? false) ? 'Yes' : 'No' }}</span></td></tr>
                                <tr><td class="fw-semibold text-gray-700">Hourly cap</td><td class="text-end fw-bold">{{ $settings['hourly_limit'] ?? 60 }}</td></tr>
                                <tr><td class="fw-semibold text-gray-700">Daily cap</td><td class="text-end fw-bold">{{ $settings['daily_limit'] ?? 300 }}</td></tr>
                                <tr><td class="fw-semibold text-gray-700">Batch pause every</td><td class="text-end fw-bold">{{ $settings['batch_pause_every'] ?? 25 }} messages</td></tr>
                                <tr><td class="fw-semibold text-gray-700">Batch pause duration</td><td class="text-end fw-bold">{{ $settings['batch_pause_min_seconds'] ?? 180 }}–{{ $settings['batch_pause_max_seconds'] ?? 420 }}s</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4 mt-4">
                        <i class="bi bi-info-circle fs-2x text-primary me-4"></i>
                        <div class="fw-semibold text-gray-700">
                            If the limiter pauses sending, messages remain <strong>pending</strong> and continue later. They are not marked failed.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card card-xl-stretch">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Operational Snapshot</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Read-only health indicators</span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex justify-content-between border-bottom pb-3">
                            <span class="fw-semibold text-gray-700">Failed today</span>
                            <span class="fw-bold text-{{ $failedToday > 0 ? 'danger' : 'success' }}">{{ $failedToday }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-3">
                            <span class="fw-semibold text-gray-700">Last successful send</span>
                            <span class="fw-bold text-gray-800">{{ optional($lastSent)->updated_at ? $lastSent->updated_at->format('Y-m-d H:i') : 'None' }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-3">
                            <span class="fw-semibold text-gray-700">Last failed send</span>
                            <span class="fw-bold text-gray-800">{{ optional($lastFailed)->updated_at ? $lastFailed->updated_at->format('Y-m-d H:i') : 'None' }}</span>
                        </div>
                        @if(!empty($rateLimit['reason']))
                            <div class="alert alert-danger mb-0">
                                {{ $rateLimit['reason'] }}
                            </div>
                        @else
                            <div class="alert alert-success mb-0">
                                No safety pause is active right now.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
