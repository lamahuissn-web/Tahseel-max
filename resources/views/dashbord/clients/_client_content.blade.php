<div id="kt_app_content_container" class="app-container container-xxl">

    {{-- Header Card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="symbol symbol-60px symbol-circle">
                            <img src="{{ asset('images/avatar.jpg') }}" alt="avatar" />
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">{{ $client->name }}</h4>
                            <div class="d-flex flex-wrap gap-3">
                                <span class="text-muted"><i class="bi bi-hash"></i> #{{ $client->id }}</span>
                                <span class="text-muted"><i class="bi bi-telephone"></i> {{ $client->phone ?? '—' }}</span>
                                <span class="text-muted"><i class="bi bi-geo-alt"></i> {{ $client->address1 ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        @if($client->sas_username)
                            @php
                                $radius = app(\App\Services\Radius\RadiusService::class);
                                $isOnline = $radius->isOnline($client->sas_username);
                            @endphp
                            <span class="badge fs-6 px-3 py-2 {{ $isOnline ? 'bg-success' : 'bg-secondary' }}">
                                <span class="online-dot {{ $isOnline ? 'online' : 'offline' }}"></span>
                                {{ $isOnline ? trans('clients.online') : trans('clients.offline') }}
                            </span>
                        @endif
                        @if($client->is_active == '1')
                            <span class="badge bg-success fs-6 px-3 py-2">
                                <i class="bi bi-check-circle-fill"></i> {{ trans('clients.active') }}
                            </span>
                        @else
                            <span class="badge bg-danger fs-6 px-3 py-2">
                                <i class="bi bi-x-circle-fill"></i> {{ trans('clients.inactive') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="bi bi-receipt fs-1 text-warning"></i>
                    <h5 class="mt-2 mb-0 fw-bold text-danger">${{ number_format($totalUnpaid ?? 0, 2) }}</h5>
                    <small class="text-muted">{{ trans('clients.remaining_amount') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="bi bi-speedometer2 fs-1 text-primary"></i>
                    <h5 class="mt-2 mb-0 fw-bold">{{ $client->subscription->name ?? '—' }}</h5>
                    <small class="text-muted">{{ trans('clients.subscription') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="bi bi-tag fs-1 text-success"></i>
                    <h5 class="mt-2 mb-0 fw-bold">{{ $client->price ?? '—' }}</h5>
                    <small class="text-muted">{{ trans('clients.price') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="bi bi-person fs-1 text-info"></i>
                    <h5 class="mt-2 mb-0 fw-bold" style="direction: ltr;">{{ $client->user ?? '—' }}</h5>
                    <small class="text-muted">{{ trans('clients.user') }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="row">
        {{-- Left Column: Session Info + Invoices --}}
        <div class="col-lg-8">
            {{-- Online Session Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="bi bi-wifi text-success"></i>
                        {{ trans('clients.session_info') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($client->sas_username)
                        @php
                            $radius = app(\App\Services\Radius\RadiusService::class);
                            $isOnline = $radius->isOnline($client->sas_username);
                            $clientInfo = $radius->getClientInfo($client->sas_username);
                            $traffic = $radius->getTraffic($client->sas_username);
                        @endphp
                        <div class="row">
                            <div class="col-md-6">
                                <div class="session-detail-card {{ $isOnline ? '' : 'offline' }}">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="online-dot {{ $isOnline ? 'online' : 'offline' }}"></span>
                                        <strong>{{ $isOnline ? trans('clients.connected') : trans('clients.disconnected') }}</strong>
                                    </div>
                                    <div class="session-detail-item">
                                        <span class="session-detail-label">{{ trans('clients.radius_username') }}</span>
                                        <span class="session-detail-value">{{ $client->sas_username }}</span>
                                    </div>
                                    @if($isOnline && isset($clientInfo['last_session']))
                                    <div class="session-detail-item">
                                        <span class="session-detail-label">{{ trans('clients.ip_address') }}</span>
                                        <span class="session-detail-value">{{ $clientInfo['last_session']['framed_ip'] ?? '—' }}</span>
                                    </div>
                                    <div class="session-detail-item">
                                        <span class="session-detail-label">{{ trans('clients.nas') }}</span>
                                        <span class="session-detail-value">{{ $clientInfo['last_session']['nas'] ?? '—' }}</span>
                                    </div>
                                    <div class="session-detail-item">
                                        <span class="session-detail-label">{{ trans('clients.session_time') }}</span>
                                        <span class="session-detail-value">
                                            @php
                                                $seconds = $clientInfo['last_session']['session_time'] ?? 0;
                                                $hours = floor($seconds / 3600);
                                                $minutes = floor(($seconds % 3600) / 60);
                                            @endphp
                                            {{ $hours }}h {{ $minutes }}m
                                        </span>
                                    </div>
                                    <div class="session-detail-item">
                                        <span class="session-detail-label">{{ trans('clients.last_login') }}</span>
                                        <span class="session-detail-value">{{ $clientInfo['last_login'] ?? '—' }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="session-detail-card">
                                    <strong class="mb-2 d-block">{{ trans('clients.traffic_month') }}</strong>
                                    <div class="session-detail-item">
                                        <span class="session-detail-label">{{ trans('clients.download') }}</span>
                                        <span class="session-detail-value text-primary">
                                            @php
                                                $dl = $traffic['download_bytes'] ?? 0;
                                                echo $dl > 1073741824 ? round($dl/1073741824, 2).' GB' : ($dl > 1048576 ? round($dl/1048576, 2).' MB' : round($dl/1024, 2).' KB');
                                            @endphp
                                        </span>
                                    </div>
                                    <div class="session-detail-item">
                                        <span class="session-detail-label">{{ trans('clients.upload') }}</span>
                                        <span class="session-detail-value text-success">
                                            @php
                                                $ul = $traffic['upload_bytes'] ?? 0;
                                                echo $ul > 1073741824 ? round($ul/1073741824, 2).' GB' : ($ul > 1048576 ? round($ul/1048576, 2).' MB' : round($ul/1024, 2).' KB');
                                            @endphp
                                        </span>
                                    </div>
                                    <div class="session-detail-item">
                                        <span class="session-detail-label">{{ trans('clients.total') }}</span>
                                        <span class="session-detail-value fw-bold">
                                            @php
                                                $total = $traffic['total_bytes'] ?? 0;
                                                echo $total > 1073741824 ? round($total/1073741824, 2).' GB' : ($total > 1048576 ? round($total/1048576, 2).' MB' : round($total/1024, 2).' KB');
                                            @endphp
                                        </span>
                                    </div>
                                    <div class="session-detail-item">
                                        <span class="session-detail-label">{{ trans('clients.sessions_count') }}</span>
                                        <span class="session-detail-value">{{ $traffic['sessions'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-wifi-off fs-1 d-block mb-2"></i>
                            <p>{{ trans('clients.no_radius_username') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Unpaid Invoices Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="bi bi-receipt-cutoff text-danger"></i>
                        {{ trans('clients.unpaid_invoices') }}
                        @if(count($unpaidInvoices) > 0)
                            <span class="badge bg-danger ms-2">{{ count($unpaidInvoices) }}</span>
                        @endif
                    </h6>
                    <div>
                        <strong class="text-danger">${{ number_format($totalUnpaid ?? 0, 2) }}</strong>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($unpaidInvoices) > 0)
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

        {{-- Right Column: Client Info + Quick Actions --}}
        <div class="col-lg-4">
            {{-- Client Information Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="bi bi-person-fill text-primary"></i> {{ trans('clients.client_information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted ps-0">{{ trans('clients.ID') }}</td>
                            <td class="fw-bold text-end">{{ $client->id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">{{ trans('clients.client_code') }}</td>
                            <td class="text-end">{{ $client->client_code ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">{{ trans('clients.phone') }}</td>
                            <td class="text-end" style="direction: ltr;">{{ $client->phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">{{ trans('clients.username') }}</td>
                            <td class="text-end" style="direction: ltr;">{{ $client->user ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">{{ trans('clients.radius_username') }}</td>
                            <td class="text-end" style="direction: ltr;">{{ $client->sas_username ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">{{ trans('clients.box_switch') }}</td>
                            <td class="text-end text-danger fw-bold">{{ $client->box_switch ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">{{ trans('clients.client_type') }}</td>
                            <td class="text-end">{{ $client->client_type ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">{{ trans('clients.subscription_date') }}</td>
                            <td class="text-end">{{ $client->subscription_date ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">{{ trans('clients.start_date') }}</td>
                            <td class="text-end">{{ $client->start_date ?? '—' }}</td>
                        </tr>
                        @if($client->address1)
                        <tr>
                            <td class="text-muted ps-0">{{ trans('clients.address1') }}</td>
                            <td class="text-end">{{ $client->address1 }}</td>
                        </tr>
                        @endif
                        @if($client->notes)
                        <tr>
                            <td class="text-muted ps-0">{{ trans('clients.notes') }}</td>
                            <td class="text-end"><small>{{ $client->notes }}</small></td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="bi bi-lightning text-warning"></i> {{ trans('clients.quick_actions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $client->phone ?? '');
                            $hasValidPhone = strlen($cleanPhone) >= 7 && !preg_match('/^0+$/', $cleanPhone);
                        @endphp
                        @if($hasValidPhone)
                        <button class="btn btn-success btn-sm" onclick="sendWhatsAppReminder({{ $client->id }})">
                            <i class="bi bi-whatsapp"></i> {{ trans('clients.whatsapp_send_reminder') }}
                        </button>
                        @endif
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
                        <a href="{{ route('admin.client_paid_invoices', $client->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-currency-dollar"></i> {{ trans('clients.client_invoices') }}
                        </a>
                        @if($client->sas_username)
                        <button class="btn btn-outline-danger btn-sm" onclick="confirmDisconnect({{ $client->id }})">
                            <i class="bi bi-wifi-off"></i> {{ trans('clients.disconnect_user') }}
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function sendWhatsAppReminder(clientId) {
    var btn = event.target.closest('button');
    var original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class=\"spinner-border spinner-border-sm\"></span> {{ trans("clients.sending") }}';

    $.ajax({
        url: '{{ route("admin.clients.whatsapp_reminder", ["id" => "__ID__"]) }}'.replace('__ID__', clientId),
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            if (res.success) {
                Swal.fire({ icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: '{{ trans("forms.error") }}', text: res.error });
            }
        },
        error: function() {
            Swal.fire({ icon: 'error', title: '{{ trans("forms.error") }}', text: '{{ trans("clients.whatsapp_send_failed") }}' });
        },
        complete: function() {
            btn.disabled = false;
            btn.innerHTML = original;
        }
    });
}

function confirmDisconnect(clientId) {
    Swal.fire({
        title: '{{ trans("clients.confirm_disconnect") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ trans("clients.yes_disconnect") }}',
        cancelButtonText: '{{ trans("clients.cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/clients/' + clientId + '/disconnect',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    Swal.fire({ icon: res.success ? 'success' : 'error', title: res.message, timer: 2000, showConfirmButton: false });
                    if (res.success) setTimeout(() => location.reload(), 1000);
                }
            });
        }
    });
}
</script>
