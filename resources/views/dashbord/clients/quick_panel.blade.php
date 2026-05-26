<style>
.qp-header { padding: 12px 16px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border-radius: 0; }
.qp-header .qp-name { font-size: 18px; font-weight: 700; color: #fff; }
.qp-header .qp-meta { font-size: 12px; color: rgba(255,255,255,0.85); margin-top: 4px; }

.qp-tiles { padding: 12px; gap: 8px; }
.qp-tile { border-radius: 8px; overflow: hidden; }
.qp-tile-inner { padding: 10px 8px; text-align: center; }
.qp-tile-icon { font-size: 20px; }
.qp-tile-label { font-size: 10px; color: #888; margin: 2px 0; }
.qp-tile-value { font-size: 13px; font-weight: 700; }

.qp-section { border-radius: 8px; overflow: hidden; }
.qp-section-header { padding: 8px 12px; border-bottom: 1px solid #e9ecef; font-size: 13px; }
.qp-table { font-size: 13px; }
.qp-table td { padding: 8px 12px; vertical-align: middle; }
.qp-table tr:not(:last-child) { border-bottom: 1px solid #f5f5f5; }
.qp-table td:first-child { color: #888; width: 45%; }
.qp-table td:last-child { text-align: end; font-weight: 500; }

.qp-actions-card { border-radius: 8px; overflow: hidden; }
.qp-actions { padding: 8px; }
.qp-actions .btn { padding: 8px 12px; font-size: 13px; font-weight: 600; border-radius: 6px; }

@media (max-width: 575.98px) {
    .qp-header { padding: 10px 12px; }
    .qp-header .qp-name { font-size: 16px; }
    .qp-header .qp-meta { font-size: 11px; }

    .qp-tiles { padding: 8px; gap: 6px; }
    .qp-tile-inner { padding: 8px 6px; }
    .qp-tile-icon { font-size: 16px; }
    .qp-tile-label { font-size: 9px; }
    .qp-tile-value { font-size: 11px; }

    .qp-section-header { padding: 6px 10px; font-size: 12px; }
    .qp-table { font-size: 12px; }
    .qp-table td { padding: 6px 10px; }
    .qp-table td:first-child { width: 40%; }

    .qp-actions { padding: 6px; }
    .qp-actions .btn { padding: 7px 10px; font-size: 12px; }
}
</style>

<div id="clientQuickPanelContent" data-client-id="{{ $client->id }}">

    {{-- Top Header --}}
    <div class="qp-header text-center text-white">
        <div class="qp-name">{{ $client->name }}</div>
        <div class="qp-meta">
            {{ trans('clients.ID') }}: {{ $client->id }}
            @if($client->client_code)
                &nbsp;|&nbsp; {{ $client->client_code }}
            @endif
        </div>
    </div>

    {{-- Stat Tiles --}}
    <div class="row qp-tiles">
        <div class="col-4">
            <div class="card border-0 shadow-sm h-100 qp-tile" style="border-left: 3px solid #dc3545 !important;">
                <div class="qp-tile-inner">
                    <div class="text-danger qp-tile-icon"><i class="bi bi-cash-stack"></i></div>
                    <div class="qp-tile-label">{{ trans('clients.remaining_amount') }}</div>
                    <div class="fw-bold text-danger qp-tile-value" style="cursor: pointer;" onclick="$('#clientQuickPanelModal').modal('hide'); showRemainingInvoices({{ $client->id }})">
                        {{ number_format($client->remaining_amount_total ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm h-100 qp-tile" style="border-left: 3px solid #198754 !important;">
                <div class="qp-tile-inner">
                    <div class="text-success qp-tile-icon"><i class="bi bi-currency-dollar"></i></div>
                    <div class="qp-tile-label">{{ trans('clients.price') }}</div>
                    <div class="fw-bold text-success qp-tile-value">{{ $client->price ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm h-100 qp-tile" style="border-left: 3px solid {{ $client->is_active == '1' ? '#198754' : '#dc3545' }} !important;">
                <div class="qp-tile-inner">
                    <div class="qp-tile-icon" style="color: {{ $client->is_active == '1' ? '#198754' : '#dc3545' }};">
                        <i class="bi {{ $client->is_active == '1' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                    </div>
                    <div class="qp-tile-label">{{ trans('clients.status') }}</div>
                    <div class="fw-bold qp-tile-value" style="color: {{ $client->is_active == '1' ? '#198754' : '#dc3545' }};">
                        {{ $client->is_active == '1' ? trans('clients.active') : trans('clients.inactive') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Details Table --}}
    <div class="px-3 pt-2 pb-2">
        <div class="card border-0 shadow-sm qp-section">
            <div class="qp-section-header bg-light">
                <span class="fw-semibold text-primary"><i class="bi bi-person-lines-fill me-1"></i> {{ trans('clients.client_information') }}</span>
            </div>
            <div class="p-0">
                <table class="table table-sm table-hover mb-0 qp-table">
                    <tbody>
                        <tr>
                            <td>{{ trans('clients.phone') }}</td>
                            <td style="direction: ltr;">{{ $client->phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('clients.user') }}</td>
                            <td>{{ $client->user ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('clients.box_switch') }}</td>
                            <td class="text-danger">{{ $client->box_switch ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('clients.client_type') }}</td>
                            <td><span class="badge bg-info" style="font-size: 10px;">{{ $client->client_type ?? '—' }}</span></td>
                        </tr>
                        <tr>
                            <td>{{ trans('clients.subscription') }}</td>
                            <td>
                                @if($client->subscription)
                                    <span class="badge bg-success" style="font-size: 10px;">{{ $client->subscription->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>{{ trans('clients.start_date') }}</td>
                            <td>{{ $client->start_date ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('clients.subscription_date') }}</td>
                            <td>{{ $client->subscription_date ?? '—' }}</td>
                        </tr>
                        @if($client->address1)
                        <tr>
                            <td>{{ trans('clients.address1') }}</td>
                            <td>{{ $client->address1 }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="px-3 pb-3">
        <div class="card border-0 shadow-sm qp-actions-card">
            <div class="qp-section-header bg-light">
                <span class="fw-semibold" style="color: #f39c12;"><i class="bi bi-lightning-fill me-1"></i> {{ trans('clients.quick_actions') }}</span>
            </div>
            <div class="qp-actions">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-danger" onclick="$('#clientQuickPanelModal').modal('hide'); showRemainingInvoices({{ $client->id }})">
                        <i class="bi bi-receipt-cutoff me-1"></i> {{ trans('clients.client_unpaid_invoices') }}
                    </button>
                    @can('add_client_invoice')
                    <a href="{{ route('admin.client_invoices', $client->id) }}" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-plus me-1"></i> {{ trans('clients.client_add_invoice') }}
                    </a>
                    @endcan
                    @can('update_client')
                    <a href="{{ route('admin.clients.change_status', [$client->id, $client->is_active]) }}"
                       class="btn btn-outline-warning"
                       onclick="return confirm('{{ trans('clients.change_status_msg') }}');">
                        <i class="bi bi-arrow-repeat me-1"></i> {{ trans('clients.change_status') }}
                    </a>
                    @endcan
                    <button class="btn btn-outline-primary" onclick="showClientDetails({{ $client->id }}); $('#clientQuickPanelModal').modal('hide');">
                        <i class="bi bi-person-circle me-1"></i> {{ trans('clients.view_full_details') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
