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
    @php
        $isOnline = $radiusInfo['online'] ?? false;
        $dlToday = $todayTraffic['download_bytes'] ?? 0;
        $ulToday = $todayTraffic['upload_bytes'] ?? 0;
        function fmtBytes($b) { $b = max($b,0); return $b > 1073741824 ? round($b/1073741824,2).' GB' : ($b > 1048576 ? round($b/1048576,2).' MB' : ($b > 1024 ? round($b/1024,2).' KB' : $b.' B')); }
    @endphp
    <div class="tab-pane fade" id="tabSession" role="tabpanel">

        {{-- Status + Speed --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge fs-5 px-3 py-2 {{ $isOnline ? 'bg-success' : 'bg-secondary' }}">
                        <i class="bi {{ $isOnline ? 'bi-wifi' : 'bi-wifi-off' }}"></i>
                        {{ $isOnline ? '\u0645\u062A\u0635\u0644' : '\u063A\u064A\u0631 \u0645\u062A\u0635\u0644' }}
                    </span>
                    <span style="direction: ltr;" class="fw-bold">{{ $client->sas_username }}</span>
                    @if($currentSpeed)
                    <span class="badge bg-primary fs-6 px-3 py-1 me-auto">
                        <i class="bi bi-speedometer2"></i> {{ $currentSpeed }}
                    </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Today's Traffic + Speed --}}
        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    <small class="text-muted">{{ '\u0627\u0633\u062A\u0647\u0644\u0627\u0643 \u0627\u0644\u064A\u0648\u0645' }}</small>
                    <strong class="text-primary fs-5">{{ fmtBytes($dlToday + $ulToday) }}</strong>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    <small class="text-muted">{{ '\u062A\u062D\u0645\u064A\u0644' }}</small>
                    <strong class="text-success fs-5">{{ fmtBytes($dlToday) }}</strong>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    <small class="text-muted">{{ '\u0631\u0641\u0639' }}</small>
                    <strong class="text-info fs-5">{{ fmtBytes($ulToday) }}</strong>
                </div>
            </div>
        </div>

        {{-- Scheduled Stop Alert --}}
        @if($client->radius_stop_at)
        <div class="alert alert-danger py-2 mb-3 small">
            <i class="bi bi-calendar-x"></i>
            {{ '\u0645\u062C\u062F\u0648\u0644 \u0644\u0644\u0625\u064A\u0642\u0627\u0641 \u0628\u062A\u0627\u0631\u064A\u062E: <strong>' . $client->radius_stop_at . '</strong>' }}
        </div>
        @endif

        {{-- Active Sessions --}}
        @if(count($activeSessions) > 0)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold small"><i class="bi bi-wifi text-success"></i> {{ '\u0627\u0644\u062C\u0644\u0633\u0627\u062A \u0627\u0644\u0646\u0634\u0637\u0629' }} ({{ count($activeSessions) }})</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ '\u0648\u0642\u062A \u0627\u0644\u0628\u062F\u0621' }}</th>
                                <th>IP</th>
                                <th>{{ '\u0627\u0644\u0645\u062F\u0629' }}</th>
                                <th>{{ '\u062A\u062D\u0645\u064A\u0644' }}</th>
                                <th>{{ '\u0631\u0641\u0639' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeSessions as $s)
                            @php $h = floor(($s->acctsessiontime ?? 0)/3600); $m = floor((($s->acctsessiontime ?? 0)%3600)/60); @endphp
                            <tr>
                                <td class="fw-bold">#{{ $s->radacctid }}</td>
                                <td class="small">{{ $s->acctstarttime ? date('m-d H:i', strtotime($s->acctstarttime)) : '—' }}</td>
                                <td><code>{{ $s->framedipaddress ?? '—' }}</code></td>
                                <td>{{ $h }}h {{ $m }}m</td>
                                <td>{{ fmtBytes($s->acctoutputoctets ?? 0) }}</td>
                                <td>{{ fmtBytes($s->acctinputoctets ?? 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Recent Disconnected Sessions --}}
        @if(count($recentSessions) > 0)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0 fw-bold small"><i class="bi bi-clock-history text-muted"></i> {{ '\u0622\u062E\u0631 5 \u062C\u0644\u0633\u0627\u062A \u0645\u0646\u062A\u0647\u064A\u0629' }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>{{ '\u0627\u0646\u062A\u0647\u062A' }}</th>
                                <th>IP</th>
                                <th>{{ '\u0627\u0644\u0645\u062F\u0629' }}</th>
                                <th>{{ '\u062A\u062D\u0645\u064A\u0644' }}</th>
                                <th>{{ '\u0631\u0641\u0639' }}</th>
                                <th>{{ '\u0627\u0644\u0633\u0628\u0628' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSessions as $s)
                            @php
                                $h = floor(($s->acctsessiontime ?? 0)/3600);
                                $m = floor((($s->acctsessiontime ?? 0)%3600)/60);
                                $cause = $s->acctterminatecause ?? 'Unknown';
                            @endphp
                            <tr>
                                <td class="small">{{ $s->acctstoptime ? date('m-d H:i', strtotime($s->acctstoptime)) : '—' }}</td>
                                <td><code>{{ $s->framedipaddress ?? '—' }}</code></td>
                                <td>{{ $h }}h {{ $m }}m</td>
                                <td>{{ fmtBytes($s->acctoutputoctets ?? 0) }}</td>
                                <td>{{ fmtBytes($s->acctinputoctets ?? 0) }}</td>
                                <td><span class="badge bg-secondary" style="font-size:0.6rem;">{{ $cause }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0 fw-bold small"><i class="bi bi-lightning text-warning"></i> {{ '\u0625\u062C\u0631\u0627\u0621\u0627\u062A \u0633\u0631\u064A\u0639\u0629' }}</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @if($isOnline)
                    <div class="col-6">
                        <button class="btn btn-outline-danger btn-sm w-100" onclick="radiusDisconnect({{ $client->id }})">
                            <i class="bi bi-plug"></i> {{ '\u0642\u0637\u0639 \u0627\u0644\u0627\u062A\u0635\u0627\u0644' }}
                        </button>
                    </div>
                    @endif
                    <div class="col-6">
                        <button class="btn btn-outline-warning btn-sm w-100" onclick="radiusToggle({{ $client->id }})">
                            <i class="bi bi-toggle-off"></i> {{ $client->is_active ? '\u062A\u0639\u0637\u064A\u0644' : '\u062A\u0641\u0639\u064A\u0644' }}
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-secondary btn-sm w-100" onclick="radiusScheduleStop({{ $client->id }}, '{{ $client->radius_stop_at ?? '' }}')">
                            <i class="bi bi-calendar-stop"></i> {{ $client->radius_stop_at ? '\u062A\u0639\u062F\u064A\u0644 \u0627\u0644\u062C\u062F\u0648\u0644\u0629' : '\u062C\u062F\u0648\u0644\u0629 \u0625\u064A\u0642\u0627\u0641' }}
                        </button>
                    </div>
                    @if($client->radius_stop_at)
                    <div class="col-6">
                        <button class="btn btn-outline-danger btn-sm w-100" onclick="radiusClearSchedule({{ $client->id }})">
                            <i class="bi bi-x-circle"></i> {{ '\u0625\u0644\u063A\u0627\u0621 \u0627\u0644\u062C\u062F\u0648\u0644\u0629' }}
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Last Session Info --}}
        @if(isset($radiusInfo['last_session']))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0 fw-bold small"><i class="bi bi-clock-history text-info"></i> {{ '\u0622\u062E\u0631 \u062C\u0644\u0633\u0629' }}</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted">IP</td><td class="fw-bold" style="direction:ltr;">{{ $radiusInfo['last_session']['framed_ip'] ?? '—' }}</td></tr>
                            <tr><td class="text-muted">{{ '\u0627\u0644\u0631\u0627\u0648\u062A\u0631' }}</td><td class="fw-bold" style="direction:ltr;">{{ $radiusInfo['last_session']['nas'] ?? '—' }}</td></tr>
                            <tr><td class="text-muted">{{ '\u0627\u0644\u0645\u062F\u0629' }}</td><td class="fw-bold">@php $sec = $radiusInfo['last_session']['session_time'] ?? 0; $h = floor($sec/3600); $m = floor(($sec%3600)/60); @endphp {{ $h }}h {{ $m }}m</td></tr>
                            <tr><td class="text-muted">{{ '\u0622\u062E\u0631 \u062F\u062E\u0648\u0644' }}</td><td>{{ $radiusInfo['last_login'] ?? '—' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold small">{{ '\u0627\u0633\u062A\u0647\u0644\u0627\u0643 \u0627\u0644\u0634\u0647\u0631' }}</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted">{{ '\u062A\u062D\u0645\u064A\u0644' }}</td><td class="fw-bold text-primary" style="direction:ltr;">{{ fmtBytes($radiusInfo['traffic']['download_bytes'] ?? 0) }}</td></tr>
                            <tr><td class="text-muted">{{ '\u0631\u0641\u0639' }}</td><td class="fw-bold text-success" style="direction:ltr;">{{ fmtBytes($radiusInfo['traffic']['upload_bytes'] ?? 0) }}</td></tr>
                            <tr><td class="text-muted">{{ '\u0627\u0644\u0625\u062C\u0645\u0627\u0644\u064A' }}</td><td class="fw-bold" style="direction:ltr;">{{ fmtBytes($radiusInfo['traffic']['total_bytes'] ?? 0) }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-4 text-muted">
            <i class="bi bi-wifi-off fs-1 d-block mb-2"></i>
            <p>{{ trans('clients.offline') }}</p>
        </div>
        @endif
    </div>
    @endif{{-- Invoices Tab --}}
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