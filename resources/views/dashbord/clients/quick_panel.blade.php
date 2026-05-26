<style>
.qp-summary { text-align: center; padding: 16px 12px 12px; background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%); border-radius: 10px 10px 0 0; margin-bottom: 0; }
.qp-summary .qp-name { font-size: 17px; font-weight: 700; color: #fff; display: block; margin-bottom: 4px; }
.qp-summary .qp-badges { display: flex; justify-content: center; gap: 6px; flex-wrap: wrap; }

.qp-section { border: 1px solid #e9ecef; border-radius: 10px; overflow: hidden; background: #fff; }
.qp-section-header { padding: 10px 14px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #e9ecef; }
.qp-section-header.bg-primary-soft { background: #e7f1ff; color: #0d6efd; }
.qp-section-header.bg-success-soft { background: #e8f5e9; color: #198754; }
.qp-section-header.bg-warning-soft { background: #fff8e1; color: #f39c12; }
.qp-section-header .qp-icon-circle { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; }
.qp-section-header.bg-primary-soft .qp-icon-circle { background: #0d6efd; color: #fff; }
.qp-section-header.bg-success-soft .qp-icon-circle { background: #198754; color: #fff; }
.qp-section-header.bg-warning-soft .qp-icon-circle { background: #f39c12; color: #fff; }

.qp-info-row { display: flex; align-items: center; padding: 10px 14px; gap: 10px; border-bottom: 1px solid #f0f0f0; }
.qp-info-row:last-child { border-bottom: none; }
.qp-info-icon { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
.qp-info-icon.bg-blue { background: #e7f1ff; color: #0d6efd; }
.qp-info-icon.bg-green { background: #e8f5e9; color: #198754; }
.qp-info-icon.bg-red { background: #ffebee; color: #dc3545; }
.qp-info-icon.bg-orange { background: #fff3e0; color: #e65100; }
.qp-info-icon.bg-purple { background: #f3e5f5; color: #7b1fa2; }
.qp-info-icon.bg-teal { background: #e0f2f1; color: #00695c; }
.qp-info-icon.bg-gray { background: #f5f5f5; color: #616161; }
.qp-info-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.3px; display: block; line-height: 1.2; }
.qp-info-val { font-size: 13px; font-weight: 600; color: #222; display: block; line-height: 1.3; word-break: break-word; }

.qp-remaining-chip { display: inline-flex; align-items: center; gap: 5px; background: #fff3cd; color: #856404; border: 1px solid #ffc107; padding: 5px 12px; border-radius: 20px; font-weight: 700; font-size: 14px; cursor: pointer; transition: all .2s; }
.qp-remaining-chip:hover { background: #ffe082; border-color: #f9a825; }
.qp-remaining-chip i { font-size: 12px; }

.qp-actions { padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; }
.qp-action-btn { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; border: 1px solid transparent; text-decoration: none; cursor: pointer; transition: all .15s; }
.qp-action-btn:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,.1); }
.qp-action-btn.qp-warn { background: #fff8e1; border-color: #ffc107; color: #856404; }
.qp-action-btn.qp-success { background: #e8f5e9; border-color: #66bb6a; color: #2e7d32; }
.qp-action-btn.qp-primary { background: #e7f1ff; border-color: #90caf9; color: #0d6efd; }
.qp-action-btn.qp-danger { background: #ffebee; border-color: #ef9a9a; color: #c62828; }
</style>

<div id="clientQuickPanelContent" data-client-id="{{ $client->id }}">

    <div class="qp-summary">
        <span class="qp-name">{{ $client->name }}</span>
        <div class="qp-badges">
            <span class="badge bg-light text-dark">{{ $client->client_code ?? '—' }}</span>
            @if($client->is_active == '1')
                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> {{ trans('clients.active') }}</span>
            @else
                <span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i> {{ trans('clients.inactive') }}</span>
            @endif
        </div>
    </div>

    <div class="qp-section">
        <div class="qp-section-header bg-primary-soft">
            <span class="qp-icon-circle"><i class="bi bi-person-fill"></i></span>
            {{ trans('clients.client_information') }}
        </div>

        <div class="qp-info-row">
            <span class="qp-info-icon bg-blue"><i class="bi bi-hash"></i></span>
            <div class="flex-grow-1"><span class="qp-info-label">{{ trans('clients.ID') }}</span><span class="qp-info-val"><span class="badge bg-primary">{{ $client->id }}</span></span></div>
        </div>
        <div class="qp-info-row">
            <span class="qp-info-icon bg-teal"><i class="bi bi-phone"></i></span>
            <div class="flex-grow-1"><span class="qp-info-label">{{ trans('clients.phone') }}</span><span class="qp-info-val text-success">{{ $client->phone ?? '—' }}</span></div>
        </div>
        <div class="qp-info-row">
            <span class="qp-info-icon bg-purple"><i class="bi bi-person-badge"></i></span>
            <div class="flex-grow-1"><span class="qp-info-label">{{ trans('clients.user') }}</span><span class="qp-info-val">{{ $client->user ?? '—' }}</span></div>
        </div>
        <div class="qp-info-row">
            <span class="qp-info-icon bg-red"><i class="bi bi-router"></i></span>
            <div class="flex-grow-1"><span class="qp-info-label">{{ trans('clients.box_switch') }}</span><span class="qp-info-val text-danger">{{ $client->box_switch ?? '—' }}</span></div>
        </div>
        <div class="qp-info-row">
            <span class="qp-info-icon bg-gray"><i class="bi bi-tag-fill"></i></span>
            <div class="flex-grow-1"><span class="qp-info-label">{{ trans('clients.client_type') }}</span><span class="qp-info-val"><span class="badge bg-info">{{ $client->client_type ?? '—' }}</span></span></div>
        </div>
        @if($client->address1)
        <div class="qp-info-row">
            <span class="qp-info-icon bg-gray"><i class="bi bi-geo-alt-fill"></i></span>
            <div class="flex-grow-1"><span class="qp-info-label">{{ trans('clients.address1') }}</span><span class="qp-info-val">{{ $client->address1 }}</span></div>
        </div>
        @endif
    </div>

    <div class="mt-3 qp-section">
        <div class="qp-section-header bg-success-soft">
            <span class="qp-icon-circle"><i class="bi bi-credit-card"></i></span>
            {{ trans('clients.subscription_info') }}
        </div>

        <div class="qp-info-row">
            <span class="qp-info-icon bg-green"><i class="bi bi-box2-fill"></i></span>
            <div class="flex-grow-1"><span class="qp-info-label">{{ trans('clients.subscription') }}</span><span class="qp-info-val">@if($client->subscription)<span class="badge bg-success">{{ $client->subscription->name }}</span>@else<span class="text-muted">—</span>@endif</span></div>
        </div>
        <div class="qp-info-row">
            <span class="qp-info-icon bg-teal"><i class="bi bi-currency-dollar"></i></span>
            <div class="flex-grow-1"><span class="qp-info-label">{{ trans('clients.price') }}</span><span class="qp-info-val text-success fw-bold fs-6">{{ $client->price ?? '—' }}</span></div>
        </div>
        <div class="qp-info-row">
            <span class="qp-info-icon bg-blue"><i class="bi bi-calendar-event"></i></span>
            <div class="flex-grow-1"><span class="qp-info-label">{{ trans('clients.start_date') }}</span><span class="qp-info-val">{{ $client->start_date ?? '—' }}</span></div>
        </div>
        <div class="qp-info-row">
            <span class="qp-info-icon bg-orange"><i class="bi bi-calendar-check"></i></span>
            <div class="flex-grow-1"><span class="qp-info-label">{{ trans('clients.subscription_date') }}</span><span class="qp-info-val">{{ $client->subscription_date ?? '—' }}</span></div>
        </div>
        <div class="qp-info-row">
            <span class="qp-info-icon bg-red"><i class="bi bi-exclamation-triangle-fill"></i></span>
            <div class="flex-grow-1">
                <span class="qp-info-label">{{ trans('clients.remaining_amount') }}</span>
                <span class="qp-remaining-chip" onclick="$('#clientQuickPanelModal').modal('hide'); showRemainingInvoices({{ $client->id }})">
                    {{ number_format($client->remaining_amount_total ?? 0, 2) }}
                    <i class="bi bi-chevron-left"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="mt-3 qp-section">
        <div class="qp-section-header bg-warning-soft">
            <span class="qp-icon-circle"><i class="bi bi-lightning-fill"></i></span>
            {{ trans('clients.quick_actions') }}
        </div>
        <div class="qp-actions">
            <button class="qp-action-btn qp-warn" onclick="$('#clientQuickPanelModal').modal('hide'); showRemainingInvoices({{ $client->id }})">
                <i class="bi bi-receipt-cutoff"></i> {{ trans('clients.client_unpaid_invoices') }}
            </button>
            @can('add_client_invoice')
            <a href="{{ route('admin.client_invoices', $client->id) }}" class="qp-action-btn qp-success">
                <i class="bi bi-file-earmark-plus"></i> {{ trans('clients.client_add_invoice') }}
            </a>
            @endcan
            @can('update_client')
            <a href="{{ route('admin.clients.change_status', [$client->id, $client->is_active]) }}"
               class="qp-action-btn qp-danger"
               onclick="return confirm('{{ trans('clients.change_status_msg') }}');">
                <i class="bi bi-arrow-repeat"></i> {{ trans('clients.change_status') }}
            </a>
            @endcan
            <button class="qp-action-btn qp-primary" onclick="showClientDetails({{ $client->id }}); $('#clientQuickPanelModal').modal('hide');">
                <i class="bi bi-person-circle"></i> {{ trans('clients.view_full_details') }}
            </button>
        </div>
    </div>

</div>
