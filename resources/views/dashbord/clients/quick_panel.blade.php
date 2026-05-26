<div id="clientQuickPanelContent" data-client-id="{{ $client->id }}">
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm quick-panel-card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-person-fill"></i> {{ trans('clients.client_information') }}
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.ID') }}</span>
                                <span class="quick-value"><span class="badge bg-primary">{{ $client->id }}</span></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.client_code') }}</span>
                                <span class="quick-value">{{ $client->client_code ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.name') }}</span>
                                <span class="quick-value fw-bold text-primary">{{ $client->name }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.phone') }}</span>
                                <span class="quick-value text-success">{{ $client->phone ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.user') }}</span>
                                <span class="quick-value">{{ $client->user ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.box_switch') }}</span>
                                <span class="quick-value text-danger fw-bold">{{ $client->box_switch ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.client_type') }}</span>
                                <span class="quick-value"><span class="badge bg-info">{{ $client->client_type ?? '—' }}</span></span>
                            </div>
                        </div>
                        @if($client->address1)
                        <div class="col-12">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.address1') }}</span>
                                <span class="quick-value">{{ $client->address1 }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm quick-panel-card">
                <div class="card-header bg-light">
                    <i class="bi bi-credit-card text-success"></i> {{ trans('clients.subscription_info') }}
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.subscription') }}</span>
                                <span class="quick-value">
                                    @if($client->subscription)
                                        <span class="badge bg-success">{{ $client->subscription->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.price') }}</span>
                                <span class="quick-value fw-bold text-success">{{ $client->price ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.start_date') }}</span>
                                <span class="quick-value">{{ $client->start_date ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.subscription_date') }}</span>
                                <span class="quick-value">{{ $client->subscription_date ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.remaining_amount') }}</span>
                                <span class="quick-value fw-bold text-danger remaining-trigger" onclick="$('#clientQuickPanelModal').modal('hide'); showRemainingInvoices({{ $client->id }})">{{ $client->remaining_amount_total ?? '0.00' }} ◀</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="quick-info-item">
                                <span class="quick-label">{{ trans('clients.status') }}</span>
                                <span class="quick-value">
                                    @if($client->is_active == '1')
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> {{ trans('clients.active') }}</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> {{ trans('clients.inactive') }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm quick-panel-card">
                <div class="card-header bg-light">
                    <i class="bi bi-lightning text-warning"></i> {{ trans('clients.quick_actions') }}
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-warning btn-sm" onclick="$('#clientQuickPanelModal').modal('hide'); showRemainingInvoices({{ $client->id }})">
                            <i class="bi bi-receipt-cutoff"></i> {{ trans('clients.client_unpaid_invoices') }}
                        </button>
                        @can('add_client_invoice')
                        <a href="{{ route('admin.client_invoices', $client->id) }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-file-earmark-plus"></i> {{ trans('clients.client_add_invoice') }}
                        </a>
                        @endcan
                        @can('update_client')
                        <a href="{{ route('admin.clients.change_status', [$client->id, $client->is_active]) }}"
                           class="btn btn-outline-warning btn-sm"
                           onclick="return confirm('{{ trans('clients.change_status_msg') }}');">
                            <i class="bi bi-arrow-repeat"></i> {{ trans('clients.change_status') }}
                        </a>
                        @endcan
                        <button class="btn btn-outline-primary btn-sm" onclick="showClientDetails({{ $client->id }}); $('#clientQuickPanelModal').modal('hide');">
                            <i class="bi bi-person-circle"></i> {{ trans('clients.view_full_details') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
