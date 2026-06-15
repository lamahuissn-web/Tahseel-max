<style>
.qp-header-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 12px;
    border-bottom: 2px solid #ffc107;
    margin-bottom: 12px;
}
.qp-header-summary .qp-client-name {
    font-weight: 700;
    font-size: 16px;
    color: #222;
}
.qp-header-summary .qp-status-badge {
    font-size: 12px;
    font-weight: 600;
    padding: 4px 14px;
    border-radius: 20px;
}
.qp-status-active {
    background: #d1e7dd;
    color: #0f5132;
}
.qp-status-inactive {
    background: #f8d7da;
    color: #842029;
}

.qp-info-item {
    display: flex;
    align-items: center;
    padding: 12px 14px;
    gap: 8px;
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    margin-bottom: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
.qp-info-item:last-child {
    margin-bottom: 0;
}
.qp-info-item .qp-info-label {
    font-size: 12px;
    color: #888;
    display: block;
    line-height: 1.2;
}
.qp-info-item .qp-info-value {
    font-weight: 600;
    font-size: 14px;
    color: #222;
    display: block;
    line-height: 1.3;
}
.qp-info-item .qp-info-main {
    flex: 1;
    min-width: 0;
}
.qp-info-item .qp-info-action {
    font-weight: 700;
    font-size: 15px;
    color: #dc3545;
    white-space: nowrap;
    cursor: pointer;
}
.qp-info-item .qp-info-action:hover {
    color: #bb2d3b;
}

.qp-section-title {
    font-size: 13px;
    font-weight: 700;
    color: #555;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 14px 0 8px;
}

.qp-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    margin-bottom: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    font-size: 14px;
    font-weight: 600;
    color: #222;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
}
.qp-action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.qp-action-btn:active {
    opacity: 0.85;
}
.qp-action-btn.qp-btn-danger {
    border-color: #f5c6cb;
    color: #dc3545;
}
.qp-action-btn.qp-btn-success {
    border-color: #c3e6cb;
    color: #198754;
}
.qp-action-btn.qp-btn-warning {
    border-color: #ffeeba;
    color: #856404;
}
.qp-action-btn.qp-btn-primary {
    border-color: #b6d4fe;
    color: #0d6efd;
}
</style>

<div id="clientQuickPanelContent" data-client-id="{{ $client->id }}">

    {{-- Header --}}
    <div class="qp-header-summary">
        <span class="qp-client-name">{{ $client->name }}</span>
        @if($client->is_active == '1')
            <span class="qp-status-badge qp-status-active">
                <i class="bi bi-check-circle-fill me-1"></i> {{ trans('clients.active') }}
            </span>
        @else
            <span class="qp-status-badge qp-status-inactive">
                <i class="bi bi-x-circle-fill me-1"></i> {{ trans('clients.inactive') }}
            </span>
        @endif
    </div>

    {{-- Online Status --}}
    @php
        $isOnline = false;
        $sessionInfo = null;
        if ($client->sas_username) {
            $radius = app(\App\Services\Radius\RadiusService::class);
            $isOnline = $radius->isOnline($client->sas_username);
            if ($isOnline) {
                $sessionInfo = $radius->getClientInfo($client->sas_username);
            }
        }
    @endphp

    @if($client->sas_username)
    <div class="qp-info-item" style="border-color: {{ $isOnline ? "#c3e6cb" : "#e8e8e8" }};">
        <div class="qp-info-main">
            <span class="qp-info-label">{{ trans("clients.online_status") }}</span>
            <span class="qp-info-value">
                @if($isOnline)
                    <span class="text-success"><i class="bi bi-wifi"></i> {{ trans("clients.online") }}</span>
                    @if($sessionInfo && isset($sessionInfo["last_session"]["framed_ip"]))
                        <br><small class="text-muted" style="direction: ltr;">{{ $sessionInfo["last_session"]["framed_ip"] }}</small>
                    @endif
                @else
                    <span class="text-secondary"><i class="bi bi-wifi-off"></i> {{ trans("clients.offline") }}</span>
                @endif
            </span>
        </div>
        <div class="qp-info-action" style="font-size: 12px; color: {{ $isOnline ? "#198754" : "#6c757d" }};">
            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ $isOnline ? "#198754" : "#6c757d" }};"></span>
        </div>
    </div>
    @endif

    {{-- Stat Row --}}
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="qp-info-item mb-0" style="cursor: pointer;" onclick="$('#clientQuickPanelModal').modal('hide'); showRemainingInvoices({{ $client->id }})">
                <div class="qp-info-main text-center">
                    <span class="qp-info-label">{{ trans('clients.remaining_amount') }}</span>
                    <span class="qp-info-action">{{ number_format($client->remaining_amount_total ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="qp-info-item mb-0">
                <div class="qp-info-main text-center">
                    <span class="qp-info-label">{{ trans('clients.price') }}</span>
                    <span class="qp-info-value text-success">{{ $client->price ?? '—' }}</span>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="qp-info-item mb-0">
                <div class="qp-info-main text-center">
                    <span class="qp-info-label">{{ trans('clients.subscription') }}</span>
                    <span class="qp-info-value">{{ $client->subscription->name ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Client Information --}}
    <div class="qp-section-title">{{ trans('clients.client_information') }}</div>

    <div class="qp-info-item">
        <div class="qp-info-main">
            <span class="qp-info-label">{{ trans('clients.ID') }}</span>
            <span class="qp-info-value">{{ $client->id }}</span>
        </div>
    </div>

    <div class="qp-info-item">
        <div class="qp-info-main">
            <span class="qp-info-label">{{ trans('clients.client_code') }}</span>
            <span class="qp-info-value">{{ $client->client_code ?? '—' }}</span>
        </div>
    </div>

    <div class="qp-info-item">
        <div class="qp-info-main">
            <span class="qp-info-label">{{ trans('clients.phone') }}</span>
            <span class="qp-info-value text-success" style="direction: ltr;">{{ $client->phone ?? '—' }}</span>
        </div>
    </div>

    <div class="qp-info-item">
        <div class="qp-info-main">
            <span class="qp-info-label">{{ trans('clients.user') }}</span>
            <span class="qp-info-value">{{ $client->user ?? '—' }}</span>
        </div>
    </div>

    <div class="qp-info-item">
        <div class="qp-info-main">
            <span class="qp-info-label">{{ trans('clients.box_switch') }}</span>
            <span class="qp-info-value text-danger">{{ $client->box_switch ?? '—' }}</span>
        </div>
    </div>

    <div class="qp-info-item">
        <div class="qp-info-main">
            <span class="qp-info-label">{{ trans('clients.client_type') }}</span>
            <span class="qp-info-value">{{ $client->client_type ?? '—' }}</span>
        </div>
    </div>

    <div class="qp-info-item">
        <div class="qp-info-main">
            <span class="qp-info-label">{{ trans('clients.start_date') }}</span>
            <span class="qp-info-value">{{ $client->start_date ?? '—' }}</span>
        </div>
    </div>

    <div class="qp-info-item">
        <div class="qp-info-main">
            <span class="qp-info-label">{{ trans('clients.subscription_date') }}</span>
            <span class="qp-info-value">{{ $client->subscription_date ?? '—' }}</span>
        </div>
    </div>

    @if($client->address1)
    <div class="qp-info-item">
        <div class="qp-info-main">
            <span class="qp-info-label">{{ trans('clients.address1') }}</span>
            <span class="qp-info-value">{{ $client->address1 }}</span>
        </div>
    </div>
    @endif

    {{-- Quick Actions --}}
    <div class="qp-section-title">{{ trans('clients.quick_actions') }}</div>

    <button class="qp-action-btn qp-btn-danger" onclick="$('#clientQuickPanelModal').modal('hide'); showRemainingInvoices({{ $client->id }})">
        <i class="bi bi-receipt-cutoff"></i> {{ trans('clients.client_unpaid_invoices') }}
    </button>

    @can('add_client_invoice')
    <a href="{{ route('admin.client_invoices', $client->id) }}" class="qp-action-btn qp-btn-success">
        <i class="bi bi-file-earmark-plus"></i> {{ trans('clients.client_add_invoice') }}
    </a>
    @endcan

    @can('update_client')
    <a href="{{ route('admin.clients.change_status', [$client->id, $client->is_active]) }}"
       class="qp-action-btn qp-btn-warning"
       onclick="return confirm('{{ trans('clients.change_status_msg') }}');">
        <i class="bi bi-arrow-repeat"></i> {{ trans('clients.change_status') }}
    </a>
    @endcan

    <button class="qp-action-btn qp-btn-primary" onclick="showClientDetails({{ $client->id }}); $('#clientQuickPanelModal').modal('hide');">
        <i class="bi bi-person-circle"></i> {{ trans('clients.view_full_details') }}
    </button>

</div>
