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
<div id="kt_app_content_container" class="app-container container-xxxl">

    {{-- Status Cards --}}
    <div class="row g-5 g-xl-8 mb-8">
        <div class="col-md-4">
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
        <div class="col-md-4">
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
        <div class="col-md-4">
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
    @if($failed > 0)
    <div class="d-flex justify-content-end mb-4">
        <button class="btn btn-warning" id="resendAllFailed">
            <i class="bi bi-arrow-clockwise"></i> {{ trans('clients.whatsapp_resend_all') ?? 'إعادة إرسال الكل' }}
        </button>
    </div>
    @endif

    {{-- Recent Items --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ trans('clients.whatsapp_recent_messages') ?? 'آخر الرسائل' }}</h3>
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
                                @elseif($log->status === 'failed')
                                    <span class="badge badge-danger">❌ {{ trans('clients.whatsapp_failed_send') ?? 'فشل' }}</span>
                                @else
                                    <span class="badge badge-warning">⏳ {{ trans('clients.whatsapp_pending') ?? 'قيد الانتظار' }}</span>
                                @endif
                            </td>
                            <td class="text-muted fs-7">{{ $log->created_at->format('Y-m-d h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-6">
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
