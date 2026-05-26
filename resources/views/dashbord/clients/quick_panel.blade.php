<div id="clientQuickPanelContent" data-client-id="{{ $client->id }}">

    {{-- Top Header --}}
    <div class="text-white text-center p-3" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border-radius: 0;">
        <div class="fw-bold" style="font-size: 18px;">{{ $client->name }}</div>
        <div class="mt-1" style="font-size: 12px; opacity: 0.85;">
            {{ trans('clients.ID') }}: {{ $client->id }}
            @if($client->client_code)
                &nbsp;|&nbsp; {{ $client->client_code }}
            @endif
        </div>
    </div>

    {{-- Stat Tiles --}}
    <div class="row g-2 px-3 pt-3">
        <div class="col-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 3px solid #dc3545 !important;">
                <div class="card-body text-center p-2">
                    <div class="text-danger mb-1" style="font-size: 18px;"><i class="bi bi-cash-stack"></i></div>
                    <div class="text-muted" style="font-size: 10px;">{{ trans('clients.remaining_amount') }}</div>
                    <div class="fw-bold text-danger" style="font-size: 13px; cursor: pointer;" onclick="$('#clientQuickPanelModal').modal('hide'); showRemainingInvoices({{ $client->id }})">
                        {{ number_format($client->remaining_amount_total ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 3px solid #198754 !important;">
                <div class="card-body text-center p-2">
                    <div class="text-success mb-1" style="font-size: 18px;"><i class="bi bi-currency-dollar"></i></div>
                    <div class="text-muted" style="font-size: 10px;">{{ trans('clients.price') }}</div>
                    <div class="fw-bold text-success" style="font-size: 13px;">{{ $client->price ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 3px solid {{ $client->is_active == '1' ? '#198754' : '#dc3545' }} !important;">
                <div class="card-body text-center p-2">
                    <div class="mb-1" style="font-size: 18px; color: {{ $client->is_active == '1' ? '#198754' : '#dc3545' }};">
                        <i class="bi {{ $client->is_active == '1' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                    </div>
                    <div class="text-muted" style="font-size: 10px;">{{ trans('clients.status') }}</div>
                    <div class="fw-bold" style="font-size: 13px; color: {{ $client->is_active == '1' ? '#198754' : '#dc3545' }};">
                        {{ $client->is_active == '1' ? trans('clients.active') : trans('clients.inactive') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Details Table --}}
    <div class="px-3 pt-3 pb-2">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-2" style="border-bottom: 1px solid #e9ecef;">
                <span class="fw-semibold text-primary" style="font-size: 14px;"><i class="bi bi-person-lines-fill me-1"></i> {{ trans('clients.client_information') }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" style="font-size: 13px;">
                    <tbody>
                        <tr>
                            <td class="text-muted ps-3" style="width: 40%;">{{ trans('clients.phone') }}</td>
                            <td class="fw-medium text-end pe-3" style="direction: ltr;">{{ $client->phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">{{ trans('clients.user') }}</td>
                            <td class="fw-medium text-end pe-3">{{ $client->user ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">{{ trans('clients.box_switch') }}</td>
                            <td class="fw-medium text-danger text-end pe-3">{{ $client->box_switch ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">{{ trans('clients.client_type') }}</td>
                            <td class="fw-medium text-end pe-3"><span class="badge bg-info" style="font-size: 11px;">{{ $client->client_type ?? '—' }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">{{ trans('clients.subscription') }}</td>
                            <td class="fw-medium text-end pe-3">
                                @if($client->subscription)
                                    <span class="badge bg-success" style="font-size: 11px;">{{ $client->subscription->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">{{ trans('clients.start_date') }}</td>
                            <td class="fw-medium text-end pe-3">{{ $client->start_date ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">{{ trans('clients.subscription_date') }}</td>
                            <td class="fw-medium text-end pe-3">{{ $client->subscription_date ?? '—' }}</td>
                        </tr>
                        @if($client->address1)
                        <tr>
                            <td class="text-muted ps-3">{{ trans('clients.address1') }}</td>
                            <td class="fw-medium text-end pe-3">{{ $client->address1 }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="px-3 pb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-2" style="border-bottom: 1px solid #e9ecef;">
                <span class="fw-semibold" style="font-size: 14px; color: #f39c12;"><i class="bi bi-lightning-fill me-1"></i> {{ trans('clients.quick_actions') }}</span>
            </div>
            <div class="card-body p-2">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-danger btn-sm" onclick="$('#clientQuickPanelModal').modal('hide'); showRemainingInvoices({{ $client->id }})">
                        <i class="bi bi-receipt-cutoff me-1"></i> {{ trans('clients.client_unpaid_invoices') }}
                    </button>
                    @can('add_client_invoice')
                    <a href="{{ route('admin.client_invoices', $client->id) }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-file-earmark-plus me-1"></i> {{ trans('clients.client_add_invoice') }}
                    </a>
                    @endcan
                    @can('update_client')
                    <a href="{{ route('admin.clients.change_status', [$client->id, $client->is_active]) }}"
                       class="btn btn-outline-warning btn-sm"
                       onclick="return confirm('{{ trans('clients.change_status_msg') }}');">
                        <i class="bi bi-arrow-repeat me-1"></i> {{ trans('clients.change_status') }}
                    </a>
                    @endcan
                    <button class="btn btn-outline-primary btn-sm" onclick="showClientDetails({{ $client->id }}); $('#clientQuickPanelModal').modal('hide');">
                        <i class="bi bi-person-circle me-1"></i> {{ trans('clients.view_full_details') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
