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
            {{-- Active Sessions Card --}}
            @if($client->sas_username)
            @php
                $radiusSvc = app(\App\Services\Radius\RadiusService::class);
                $activeSessions = $radiusSvc->getActiveUserSessions($client->sas_username);
                $todayTraffic = $radiusSvc->getTodayTraffic($client->sas_username);
                $isOnline = $radiusSvc->isOnline($client->sas_username);
            @endphp
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="bi bi-wifi text-success"></i> الجلسات النشطة
                    </h6>
                    <div>
                        <span class="badge {{ $isOnline ? 'bg-success' : 'bg-secondary' }} fs-6 px-3 py-1">
                            <i class="bi bi-circle-fill" style="font-size:0.5rem;"></i>
                            {{ $isOnline ? 'متصل' : 'غير متصل' }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($activeSessions) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>IP</th>
                                    <th>المدة</th>
                                    <th>تحميل</th>
                                    <th>رفع</th>
                                    <th>وقت البدء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeSessions as $s)
                                @php
                                    $h = floor(($s->acctsessiontime ?? 0) / 3600);
                                    $m = floor((($s->acctsessiontime ?? 0) % 3600) / 60);
                                @endphp
                                <tr>
                                    <td class="fw-bold">#{{ $s->radacctid }}</td>
                                    <td><code>{{ $s->framedipaddress ?? '—' }}</code></td>
                                    <td>{{ $h }}h {{ $m }}m</td>
                                    <td>{{ formatBytes($s->acctoutputoctets ?? 0) }}</td>
                                    <td>{{ formatBytes($s->acctinputoctets ?? 0) }}</td>
                                    <td class="small text-muted">{{ $s->acctstarttime ? date('Y-m-d H:i', strtotime($s->acctstarttime)) : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-3 text-muted small">
                        <i class="bi bi-wifi-off d-block mb-1 fs-4"></i>
                        <p class="mb-0">لا توجد جلسات نشطة حالياً</p>
                        <small>آخر تحديث: {{ now()->format('H:i') }}</small>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Today Traffic Mini Stats --}}
            @php
                $dlToday = $todayTraffic['download_bytes'] ?? 0;
                $ulToday = $todayTraffic['upload_bytes'] ?? 0;
                $totalToday = $dlToday + $ulToday;
            @endphp
            <div class="row g-2 mb-4">
                <div class="col-4">
                    <div class="card shadow-sm border-0 text-center py-2">
                        <small class="text-muted">استهلاك اليوم</small>
                        <strong class="text-primary">{{ formatBytes($totalToday) }}</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card shadow-sm border-0 text-center py-2">
                        <small class="text-muted">تحميل</small>
                        <strong class="text-success">{{ formatBytes($dlToday) }}</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card shadow-sm border-0 text-center py-2">
                        <small class="text-muted">رفع</small>
                        <strong class="text-info">{{ formatBytes($ulToday) }}</strong>
                    </div>
                </div>
            </div>
            @endif

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