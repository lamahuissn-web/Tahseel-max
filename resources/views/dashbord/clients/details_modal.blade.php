<div class="row">
    <!-- Client Information Section -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-person-fill text-primary"></i> {{ trans('clients.client_information') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <strong class="text-muted me-2">{{ trans('clients.ID') }}:</strong>
                            <span class="badge bg-primary">{{ $client->id }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <strong class="text-muted me-2">{{ trans('clients.client_code') }}:</strong>
                            <span>{{ $client->client_code ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <strong class="text-muted me-2">{{ trans('clients.name') }}:</strong>
                            <span class="fw-bold text-primary">{{ $client->name }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <strong class="text-muted me-2">{{ trans('clients.phone') }}:</strong>
                            <span class="text-success">{{ $client->phone ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <strong class="text-muted me-2">{{ trans('clients.email') }}:</strong>
                            <span>{{ $client->email ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <strong class="text-muted me-2">{{ trans('clients.client_type') }}:</strong>
                            <span class="badge bg-info">{{ $client->client_type ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <strong class="text-muted me-2">{{ trans('clients.user') }}:</strong>
                            <span>{{ $client->user ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <strong class="text-muted me-2">{{ trans('clients.box_switch') }}:</strong>
                            <span class="text-danger fw-bold">{{ $client->box_switch ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-2">
                            <strong class="text-muted">{{ trans('clients.address1') }}:</strong>
                            <p class="mb-0 mt-1">{{ $client->address1 ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-2">
                            <strong class="text-muted">{{ trans('clients.notes') }}:</strong>
                            <p class="mb-0 mt-1 text-muted">{{ $client->notes ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription & Status Section -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-credit-card text-success"></i> {{ trans('clients.subscription_info') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">{{ trans('clients.subscription') }}:</label>
                    <div class="mt-1">
                        @if($client->subscription)
                            <span class="badge bg-success fs-6">{{ $client->subscription->name }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">{{ trans('clients.price') }}:</label>
                    <div class="mt-1">
                        <span class="fw-bold text-success fs-5">{{ $client->price ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">{{ trans('clients.start_date') }}:</label>
                    <div class="mt-1">
                        <span>{{ $client->start_date ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">{{ trans('clients.subscription_date') }}:</label>
                    <div class="mt-1">
                        <span>{{ $client->subscription_date ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">{{ trans('clients.remaining_amount') }}:</label>
                    <div class="mt-1">
                        <span class="fw-bold text-danger fs-5">{{ $client->remaining_amount_total ?? 0 }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">{{ trans('clients.status') }}:</label>
                    <div class="mt-1">
                        @if($client->is_active == '1')
                            <span class="badge bg-success fs-6">
                                <i class="bi bi-check-circle-fill"></i> {{ trans('clients.active') }}
                            </span>
                        @else
                            <span class="badge bg-danger fs-6">
                                <i class="bi bi-x-circle-fill"></i> {{ trans('clients.inactive') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-lightning text-warning"></i> {{ trans('clients.quick_actions') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.client_paid_invoices', $client->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-currency-dollar"></i> {{ trans('clients.client_invoices') }}
                    </a>
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Information -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-info-circle text-info"></i> {{ trans('clients.additional_info') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">{{ trans('clients.created_at') }}:</small>
                        <p class="mb-1">{{ $client->created_at ? $client->created_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">{{ trans('clients.updated_at') }}:</small>
                        <p class="mb-1">{{ $client->updated_at ? $client->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
