<ul class="nav nav-tabs mb-3" id="clientDetailTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#tabDetails" type="button" role="tab">
            <i class="bi bi-person-fill"></i> {{ trans('clients.details') }}
        </button>
    </li>
    @if($client->sas_username)
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="session-tab" data-bs-toggle="tab" data-bs-target="#tabSession" type="button" role="tab">
            <i class="bi bi-wifi"></i> {{ trans('clients.session_info') }}
        </button>
    </li>
    @endif
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#tabInvoices" type="button" role="tab">
            <i class="bi bi-receipt"></i> {{ trans('clients.unpaid_invoices') }}
            @if(count($unpaidInvoices ?? []) > 0)
                <span class="badge bg-danger">{{ count($unpaidInvoices) }}</span>
            @endif
        </button>
    </li>
</ul>

<div class="tab-content" id="clientDetailTabContent">
    {{-- Details Tab --}}
    <div class="tab-pane fade show active" id="tabDetails" role="tabpanel">
        <div class="row">
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
                                    <strong class="text-muted me-2">{{ trans('clients.radius_username') }}:</strong>
                                    <span style="direction: ltr;">{{ $client->sas_username ?? 'N/A' }}</span>
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
                            <label class="form-label fw-bold text-muted">{{ trans('clients.remaining_amount') }}:</label>
                            <div class="mt-1">
                                <span class="fw-bold text-danger fs-5">${{ number_format($totalUnpaid ?? 0, 2) }}</span>
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
    </div>

    {{-- Session Tab --}}
    @if($client->sas_username)
    <div class="tab-pane fade" id="tabSession" role="tabpanel">
        @php
            $isOnline = $radiusInfo['online'] ?? false;
        @endphp
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="badge fs-5 px-3 py-2 {{ $isOnline ? 'bg-success' : 'bg-secondary' }}">
                        <i class="bi {{ $isOnline ? 'bi-wifi' : 'bi-wifi-off' }}"></i>
                        {{ $isOnline ? trans('clients.online') : trans('clients.offline') }}
                    </span>
                    <span style="direction: ltr;">{{ $client->sas_username }}</span>
                </div>

                @if($isOnline && isset($radiusInfo['last_session']))
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">{{ trans('clients.ip_address') }}</td>
                                <td class="fw-bold" style="direction: ltr;">{{ $radiusInfo['last_session']['framed_ip'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ trans('clients.nas_device') }}</td>
                                <td class="fw-bold" style="direction: ltr;">{{ $radiusInfo['last_session']['nas'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ trans('clients.session_time') }}</td>
                                <td class="fw-bold">
                                    @php
                                        $seconds = $radiusInfo['last_session']['session_time'] ?? 0;
                                        $hours = floor($seconds / 3600);
                                        $minutes = floor(($seconds % 3600) / 60);
                                    @endphp
                                    {{ $hours }}h {{ $minutes }}m
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ trans('clients.last_login') }}</td>
                                <td>{{ $radiusInfo['last_login'] ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">{{ trans('clients.traffic_month') }}</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">{{ trans('clients.download') }}</td>
                                <td class="fw-bold text-primary" style="direction: ltr;">
                                    @php
                                        $dl = $radiusInfo['traffic']['download_bytes'] ?? 0;
                                        echo $dl > 1073741824 ? round($dl/1073741824, 2).' GB' : ($dl > 1048576 ? round($dl/1048576, 2).' MB' : round($dl/1024, 2).' KB');
                                    @endphp
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ trans('clients.upload') }}</td>
                                <td class="fw-bold text-success" style="direction: ltr;">
                                    @php
                                        $ul = $radiusInfo['traffic']['upload_bytes'] ?? 0;
                                        echo $ul > 1073741824 ? round($ul/1073741824, 2).' GB' : ($ul > 1048576 ? round($ul/1048576, 2).' MB' : round($ul/1024, 2).' KB');
                                    @endphp
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ trans('clients.total_traffic') }}</td>
                                <td class="fw-bold" style="direction: ltr;">
                                    @php
                                        $t = $radiusInfo['traffic']['total_bytes'] ?? 0;
                                        echo $t > 1073741824 ? round($t/1073741824, 2).' GB' : ($t > 1048576 ? round($t/1048576, 2).' MB' : round($t/1024, 2).' KB');
                                    @endphp
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-wifi-off fs-1 d-block mb-2"></i>
                    <p>{{ trans('clients.offline') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Invoices Tab --}}
    <div class="tab-pane fade" id="tabInvoices" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-receipt-cutoff text-danger"></i> {{ trans('clients.unpaid_invoices') }}
                </h6>
                <strong class="text-danger">${{ number_format($totalUnpaid ?? 0, 2) }}</strong>
            </div>
            <div class="card-body p-0">
                @if(count($unpaidInvoices ?? []) > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ trans('invoices.invoice_date') }}</th>
                                <th>{{ trans('invoices.subscription') }}</th>
                                <th class="text-end">{{ trans('invoices.amount') }}</th>
                                <th class="text-end">{{ trans('invoices.remaining') }}</th>
                                <th>{{ trans('invoices.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unpaidInvoices as $inv)
                            <tr>
                                <td>{{ $inv->id }}</td>
                                <td>{{ $inv->due_date ? date('m-Y', strtotime($inv->due_date)) : '—' }}</td>
                                <td>{{ $inv->subscription->name ?? '—' }}</td>
                                <td class="text-end">${{ number_format($inv->amount ?? 0, 2) }}</td>
                                <td class="text-end text-danger fw-bold">${{ number_format($inv->remaining_amount ?? 0, 2) }}</td>
                                <td>
                                    @if($inv->status == 'unpaid')
                                        <span class="badge bg-danger">{{ trans('invoices.unpaid') }}</span>
                                    @elseif($inv->status == 'partial')
                                        <span class="badge bg-warning">{{ trans('invoices.partial') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                    <p>{{ trans('clients.no_unpaid_invoices') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
