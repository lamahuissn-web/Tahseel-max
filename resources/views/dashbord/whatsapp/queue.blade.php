@extends('dashbord.layouts.master')

@section('title')
{{ trans('clients.whatsapp_queue') ?? 'طابور الإرسال' }}
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('clients.whatsapp_queue') ?? 'طابور الإرسال';
    $breadcrumbs = [
        ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
        ['label' => trans('clients.whatsapp_control_center'), 'link' => route('admin.whatsapp.dashboard')],
        ['label' => trans('clients.whatsapp_queue') ?? 'طابور الإرسال', 'link' => ''],
    ];
    PageTitle($title, $breadcrumbs);
    @endphp
</div>
@endsection

@section('content')

@include('dashbord.whatsapp._partials.tab-nav')
<div id="kt_app_content_container" class="app-container container-xxxl">

    {{-- Status Cards --}}
    <div class="row g-5 g-xl-8 mb-8">
        <div class="col-md-3">
            <div class="card card-xl-stretch">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-warning-light">
                            <i class="bi bi-hourglass fs-2x text-warning"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fs-2x fw-bold text-gray-800">{{ $pending }}</span>
                        <span class="text-muted fs-7">{{ trans('clients.whatsapp_pending') ?? 'قيد الانتظار' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-xl-stretch">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-primary-light">
                            <i class="bi bi-arrow-repeat fs-2x text-primary"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fs-2x fw-bold text-gray-800">{{ $sending }}</span>
                        <span class="text-muted fs-7">{{ trans('clients.whatsapp_sending') ?? 'جارٍ الإرسال' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-xl-stretch">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-danger-light">
                            <i class="bi bi-x-circle fs-2x text-danger"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fs-2x fw-bold text-gray-800">{{ $failed }}</span>
                        <span class="text-muted fs-7">{{ trans('clients.whatsapp_failed_count') ?? 'فاشلة' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-xl-stretch">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-{{ $queuePaused ? 'danger' : 'success' }}-light">
                            <i class="bi bi-{{ $queuePaused ? 'pause-circle' : 'play-circle' }} fs-2x text-{{ $queuePaused ? 'danger' : 'success' }}"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fs-6 fw-bold text-gray-800">
                            {{ $queuePaused ? (trans('clients.whatsapp_paused') ?? 'متوقف') : (trans('clients.whatsapp_active') ?? 'نشط') }}
                        </span>
                        <button class="btn btn-sm {{ $queuePaused ? 'btn-success' : 'btn-warning' }} mt-2" id="togglePause">
                            {{ $queuePaused ? (trans('clients.whatsapp_resume') ?? 'استئناف') : (trans('clients.whatsapp_pause_queue') ?? 'إيقاف مؤقت') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="card mb-6">
        <div class="card-body py-4">
            <form method="GET" action="{{ route('admin.whatsapp.queue') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fs-7">{{ trans('clients.status') ?? 'الحالة' }}</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="" {{ ($statusFilter ?? '') === '' ? 'selected' : '' }}>{{ trans('clients.all') ?? 'الكل' }}</option>
                        <option value="pending" {{ ($statusFilter ?? '') === 'pending' ? 'selected' : '' }}>⏳ {{ trans('clients.whatsapp_pending') ?? 'قيد الانتظار' }}</option>
                        <option value="sending" {{ ($statusFilter ?? '') === 'sending' ? 'selected' : '' }}>🔄 {{ trans('clients.whatsapp_sending') ?? 'جارٍ الإرسال' }}</option>
                        <option value="sent" {{ ($statusFilter ?? '') === 'sent' ? 'selected' : '' }}>✅ {{ trans('clients.whatsapp_sent') ?? 'تم الإرسال' }}</option>
                        <option value="failed" {{ ($statusFilter ?? '') === 'failed' ? 'selected' : '' }}>❌ {{ trans('clients.whatsapp_failed_send') ?? 'فشل' }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-7">{{ trans('clients.sender') ?? 'المصدر' }}</label>
                    <select name="source" class="form-select form-select-sm">
                        @foreach(($sourceOptions ?? []) as $value => $label)
                            <option value="{{ $value }}" {{ ($sourceFilter ?? '') === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-funnel"></i> {{ trans('clients.whatsapp_apply_filter') ?? 'تطبيق' }}
                    </button>
                    <a href="{{ route('admin.whatsapp.queue') }}" class="btn btn-sm btn-light">
                        {{ trans('clients.all') ?? 'الكل' }}
                    </a>
                </div>
                <div class="col-md-3 d-flex justify-content-md-end">
                    @if($failed > 0)
                    <button type="button" class="btn btn-warning" id="resendAllFailed">
                        <i class="bi bi-arrow-clockwise"></i> {{ trans('clients.whatsapp_resend_all') ?? 'إعادة إرسال الكل' }}
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Batch Summary --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">{{ trans('clients.whatsapp_queue') ?? 'طابور الإرسال' }} — Batch Summary</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-align-middle">
                    <thead>
                        <tr class="fw-bold fs-6 text-gray-800">
                            <th>{{ trans('clients.sender') ?? 'المصدر' }}</th>
                            <th>Batch</th>
                            <th>Total</th>
                            <th>Pending</th>
                            <th>Sending</th>
                            <th>Sent</th>
                            <th>Failed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($batchSummaries ?? []) as $batch)
                        <tr>
                            <td><span class="badge {{ $batch['source_badge'] }}">{{ $batch['source_label'] }}</span></td>
                            <td class="text-muted fs-7">{{ $batch['batch_label'] }}</td>
                            <td><span class="badge badge-light">{{ $batch['total'] }}</span></td>
                            <td><span class="badge badge-light-warning">{{ $batch['pending'] }}</span></td>
                            <td><span class="badge badge-light-primary">{{ $batch['sending'] }}</span></td>
                            <td><span class="badge badge-light-success">{{ $batch['sent'] }}</span></td>
                            <td><span class="badge badge-light-danger">{{ $batch['failed'] }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-6">No batches found for current filter.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Items --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ trans('clients.whatsapp_recent_messages') ?? 'آخر الرسائل' }}</h3>
            <div class="card-toolbar text-muted fs-8">
                Showing {{ count($recent ?? []) }} rows
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-align-middle">
                    <thead>
                        <tr class="fw-bold fs-6 text-gray-800">
                            <th>{{ trans('clients.client_name') ?? 'الزبون' }}</th>
                            <th>{{ trans('clients.phone') ?? 'الهاتف' }}</th>
                            <th>{{ trans('clients.whatsapp_template_type') ?? 'القالب' }}</th>
                            <th>{{ trans('clients.status') ?? 'الحالة' }}</th>
                            <th>{{ trans('clients.sender') ?? 'الدفعة' }}</th>
                            <th>{{ trans('clients.date') ?? 'التاريخ' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent as $log)
                        <tr>
                            <td>{{ $log->client_name ?? '-' }}</td>
                            <td>{{ $log->phone }}</td>
                            <td><span class="badge badge-light">{{ $log->template_type ?? '-' }}</span></td>
                            <td>
                                @if($log->status === 'sent')
                                    <span class="badge badge-success">✅ {{ trans('clients.whatsapp_sent') ?? 'تم الإرسال' }}</span>
                                @elseif($log->status === 'sending')
                                    <span class="badge badge-primary">🔄 {{ trans('clients.whatsapp_sending') ?? 'جارٍ الإرسال' }}</span>
                                @elseif($log->status === 'failed')
                                    <span class="badge badge-danger">❌ {{ trans('clients.whatsapp_failed_send') ?? 'فشل' }}</span>
                                @else
                                    <span class="badge badge-warning">⏳ {{ trans('clients.whatsapp_pending') ?? 'قيد الانتظار' }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $sentBy = trim((string) ($log->sent_by ?? ''));
                                    $sourceLabel = 'System';
                                    $sourceBadge = 'badge-light-dark';
                                    $sourceDetail = '-';

                                    if ($sentBy !== '') {
                                        $sourceDetail = $sentBy;
                                        if (str_contains($sentBy, 'admin:manual|batch:')) {
                                            $sourceLabel = 'Manual Bulk';
                                            $sourceBadge = 'badge-light-primary';
                                            $sourceDetail = 'Batch ' . substr(explode('|batch:', $sentBy, 2)[1] ?? '', 0, 8);
                                        } elseif (str_contains($sentBy, 'admin:automation|batch:')) {
                                            $sourceLabel = 'Automation';
                                            $sourceBadge = 'badge-light-success';
                                            $sourceDetail = 'Batch ' . substr(explode('|batch:', $sentBy, 2)[1] ?? '', 0, 8);
                                        } elseif (str_contains($sentBy, 'system:autoreceipt')) {
                                            $sourceLabel = 'Auto Receipt';
                                            $sourceBadge = 'badge-light-warning';
                                            $sourceDetail = 'Batch ' . substr(explode('|batch:', $sentBy, 2)[1] ?? '', 0, 8);
                                        } elseif (str_starts_with($sentBy, 'calendar:')) {
                                            $sourceLabel = 'Calendar';
                                            $sourceBadge = 'badge-light-warning';
                                        } elseif (str_starts_with($sentBy, 'admin:')) {
                                            $sourceLabel = 'Manual Single';
                                            $sourceBadge = 'badge-light-primary';
                                        } elseif (str_starts_with($sentBy, 'hermes:')) {
                                            $sourceLabel = 'Test/Hermes';
                                            $sourceBadge = 'badge-light-info';
                                        } elseif (str_starts_with($sentBy, 'cron:')) {
                                            $sourceLabel = 'Cron';
                                            $sourceBadge = 'badge-light-success';
                                        } else {
                                            $sourceLabel = 'Other';
                                        }
                                    }
                                @endphp
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge {{ $sourceBadge }}">{{ $sourceLabel }}</span>
                                    <span class="text-muted fs-8">{{ $sourceDetail }}</span>
                                </div>
                            </td>
                            <td class="text-muted fs-7">{{ $log->created_at->format('Y-m-d h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-6">
                                {{ trans('clients.whatsapp_no_messages') ?? 'لا توجد رسائل بعد' }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    @if(($pending ?? 0) > 0 || ($sending ?? 0) > 0)
    setTimeout(function() {
        window.location.reload();
    }, 5000);
    @endif

    $('#togglePause').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');
        $.post('{{ route("admin.whatsapp.queue.pause") }}', {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            if (res.paused) {
                btn.removeClass('btn-success').addClass('btn-warning')
                    .text('{{ trans("clients.whatsapp_pause_queue") ?? "إيقاف مؤقت" }}');
                Swal.fire({ icon: 'info', text: '{{ trans("clients.whatsapp_queue_paused") ?? "تم إيقاف الطابور" }}', timer: 2000 });
            } else {
                btn.removeClass('btn-warning').addClass('btn-success')
                    .text('{{ trans("clients.whatsapp_resume") ?? "استئناف" }}');
                Swal.fire({ icon: 'success', text: '{{ trans("clients.whatsapp_queue_resumed") ?? "تم استئناف الطابور" }}', timer: 2000 });
            }
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false);
        });
    });

    $('#resendAllFailed').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i> {{ trans("clients.whatsapp_sending") ?? "جارٍ الإرسال..." }}');
        $.post('{{ route("admin.whatsapp.queue.resend_failed") }}', {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            Swal.fire({
                icon: (res.resent > 0) ? 'success' : 'info',
                text: '{{ trans("clients.whatsapp_resend_results") ?? "تمت إعادة إرسال" }} ' + res.resent + '، ' + (res.still_failed || 0) + ' {{ trans("clients.whatsapp_still_failed") ?? "لسا فاشلة" }}'
            });
            location.reload();
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i> {{ trans("clients.whatsapp_resend_all") ?? "إعادة إرسال الكل" }}');
        });
    });
});
</script>
@endsection
