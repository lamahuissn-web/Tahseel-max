@extends('dashbord.layouts.master')

@section('title')
{{ trans('clients.whatsapp_automation') ?? 'التشغيل الآلي' }}
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('clients.whatsapp_automation') ?? 'التشغيل الآلي';
    $breadcrumbs = [
        ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
        ['label' => trans('clients.whatsapp_control_center'), 'link' => route('admin.whatsapp.dashboard')],
        ['label' => trans('clients.whatsapp_automation') ?? 'التشغيل الآلي', 'link' => ''],
    ];
    PageTitle($title, $breadcrumbs);
    @endphp
</div>
@endsection

@section('content')
<div id="kt_app_content_container" class="app-container container-xxxl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ trans('clients.whatsapp_automation_rules') ?? 'قواعد التشغيل الآلي' }}</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-align-middle">
                    <thead>
                        <tr class="fw-bold fs-6 text-gray-800">
                            <th>{{ trans('clients.whatsapp_rule') ?? 'القاعدة' }}</th>
                            <th>{{ trans('clients.whatsapp_command') ?? 'الأمر' }}</th>
                            <th>{{ trans('clients.status') ?? 'الحالة' }}</th>
                            <th>{{ trans('clients.whatsapp_actions') ?? 'إجراءات' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                        <tr>
                            <td>
                                <span class="fw-bold">{{ app()->getLocale() == 'ar' ? $rule['label'] : $rule['label_en'] }}</span>
                            </td>
                            <td><code>{{ $rule['command'] }}</code></td>
                            <td>
                                <span class="badge {{ $rule['enabled'] ? 'badge-success' : 'badge-secondary' }}" id="status-{{ $rule['id'] }}">
                                    {{ $rule['enabled'] ? '🟢 ' . (trans('clients.active') ?? 'مفعل') : '⚪ ' . (trans('clients.inactive') ?? 'معطل') }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm {{ $rule['enabled'] ? 'btn-warning' : 'btn-success' }} toggle-rule"
                                        data-id="{{ $rule['id'] }}">
                                    {{ $rule['enabled'] ? (trans('clients.whatsapp_disable') ?? 'تعطيل') : (trans('clients.whatsapp_enable') ?? 'تفعيل') }}
                                </button>
                                <button class="btn btn-sm btn-primary run-rule" data-id="{{ $rule['command'] }}">
                                    <i class="bi bi-play-fill"></i> {{ trans('clients.whatsapp_run_now') ?? 'تشغيل الآن' }}
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-6">
                                {{ trans('clients.whatsapp_no_rules') ?? 'لا توجد قواعد تشغيل آلي' }}
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
    $('.toggle-rule').on('click', function() {
        const id = $(this).data('id');
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');
        $.post('{{ url("admin/whatsapp/automation") }}/' + id + '/toggle', {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            if (res.enabled) {
                $('#status-' + id).removeClass('badge-secondary').addClass('badge-success')
                    .text('🟢 {{ trans("clients.active") ?? "مفعل" }}');
                btn.removeClass('btn-success').addClass('btn-warning')
                    .text('{{ trans("clients.whatsapp_disable") ?? "تعطيل" }}');
            } else {
                $('#status-' + id).removeClass('badge-success').addClass('badge-secondary')
                    .text('⚪ {{ trans("clients.inactive") ?? "معطل" }}');
                btn.removeClass('btn-warning').addClass('btn-success')
                    .text('{{ trans("clients.whatsapp_enable") ?? "تفعيل" }}');
            }
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false);
        });
    });

    $('.run-rule').on('click', function() {
        const id = $(this).data('id');
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner"></i>');
        Swal.fire({ icon: 'info', text: '{{ trans("clients.whatsapp_running") ?? "جارٍ التشغيل..." }}', showConfirmButton: false });
        $.post('{{ url("admin/whatsapp/automation") }}/' + id + '/run', {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            Swal.fire({ icon: res.success ? 'success' : 'error', text: res.output || res.error });
        }).fail(function() {
            Swal.fire({ icon: 'error', text: '{{ trans("clients.whatsapp_test_error") ?? "حدث خطأ" }}' });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="bi bi-play-fill"></i> {{ trans("clients.whatsapp_run_now") ?? "تشغيل الآن" }}');
        });
    });
});
</script>
@endsection
