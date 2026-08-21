@extends('dashbord.layouts.master')

@section('title')
WhatsApp Safety
@endsection

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
        $title = 'WhatsApp Safety';
        $breadcrumbs = [
            ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
            ['label' => trans('clients.whatsapp_control_center'), 'link' => route('admin.whatsapp.dashboard')],
            ['label' => 'Safety', 'link' => ''],
        ];
        PageTitle($title, $breadcrumbs);
    @endphp
</div>
@endsection

@section('content')
@include('dashbord.whatsapp._partials.tab-nav')

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center mb-6">
        <i class="bi bi-check-circle-fill fs-2 me-3"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger mb-6">
        <div class="fw-bold mb-2">لم يتم حفظ الإعدادات:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $riskLevel = $rateLimit['risk_level'] ?? 'safe';
    $riskMap = [
        'safe' => ['class' => 'success', 'label' => 'Safe', 'icon' => 'bi-shield-check', 'text' => 'Sending is inside the configured safety limits.'],
        'warning' => ['class' => 'warning', 'label' => 'Warning', 'icon' => 'bi-exclamation-triangle', 'text' => 'Sending is near one of the configured limits. Slow down or let the queue continue later.'],
        'paused' => ['class' => 'danger', 'label' => 'Paused', 'icon' => 'bi-pause-circle', 'text' => $rateLimit['reason'] ?? 'Sending is paused by the safety limiter.'],
        'disabled' => ['class' => 'secondary', 'label' => 'Disabled', 'icon' => 'bi-shield-x', 'text' => 'Rate limiter is disabled. This is not recommended for OpenWA.'],
    ];
    $risk = $riskMap[$riskLevel] ?? $riskMap['safe'];
    $settings = $rateLimit['settings'] ?? [];
    $canUpdateSafety = auth('admin')->user()?->can('update_whatsapp_safety_settings') === true;
@endphp

<div id="kt_app_content_container" class="app-container container-xxxl">
    <div class="alert alert-{{ $risk['class'] }} d-flex align-items-center p-5 mb-8">
        <i class="bi {{ $risk['icon'] }} fs-2x me-4"></i>
        <div class="d-flex flex-column">
            <h4 class="mb-1">🛡️ WhatsApp Safety: {{ $risk['label'] }}</h4>
            <span>{{ $risk['text'] }}</span>
        </div>
        <div class="ms-auto text-end d-none d-md-block">
            <span class="badge badge-light fs-7">Checked: {{ optional($rateLimit['checked_at'] ?? null)->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s') }}</span>
        </div>
    </div>

    <div class="row g-5 g-xl-8 mb-8">
        <div class="col-xl-3 col-md-6">
            <div class="card card-xl-stretch">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="symbol symbol-45px me-4"><span class="symbol-label bg-primary-light"><i class="bi bi-clock-history fs-2x text-primary"></i></span></span>
                        <div>
                            <div class="fw-bold text-gray-800">Last Hour</div>
                            <div class="text-muted fs-7">Hourly cap usage</div>
                        </div>
                    </div>
                    <div class="fs-2x fw-bold text-gray-900" dir="ltr">{{ $rateLimit['hourly_sent'] ?? 0 }} / {{ $settings['hourly_limit'] ?? 60 }}</div>
                    <div class="progress h-8px mt-3">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $rateLimit['hourly_percent'] ?? 0 }}%"></div>
                    </div>
                    <div class="text-muted fs-8 mt-2">{{ $rateLimit['hourly_percent'] ?? 0 }}% used</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-xl-stretch">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="symbol symbol-45px me-4"><span class="symbol-label bg-info-light"><i class="bi bi-calendar-day fs-2x text-info"></i></span></span>
                        <div>
                            <div class="fw-bold text-gray-800">Today</div>
                            <div class="text-muted fs-7">Daily cap usage</div>
                        </div>
                    </div>
                    <div class="fs-2x fw-bold text-gray-900" dir="ltr">{{ $rateLimit['daily_sent'] ?? 0 }} / {{ $settings['daily_limit'] ?? 300 }}</div>
                    <div class="progress h-8px mt-3">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $rateLimit['daily_percent'] ?? 0 }}%"></div>
                    </div>
                    <div class="text-muted fs-8 mt-2">{{ $rateLimit['daily_percent'] ?? 0 }}% used</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-xl-stretch">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="symbol symbol-45px me-4"><span class="symbol-label bg-success-light"><i class="bi bi-hourglass-split fs-2x text-success"></i></span></span>
                        <div>
                            <div class="fw-bold text-gray-800">Delay Range</div>
                            <div class="text-muted fs-7">Randomized anti-pattern timing</div>
                        </div>
                    </div>
                    <div class="fs-2x fw-bold text-gray-900" dir="ltr">{{ $rateLimit['delay_min_seconds'] ?? 0 }}–{{ $rateLimit['delay_max_seconds'] ?? 0 }}s</div>
                    <div class="text-muted fs-8 mt-2">Base {{ $settings['base_delay'] ?? 10 }}s, jitter ±{{ $settings['jitter_percent'] ?? 40 }}%</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-xl-stretch">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="symbol symbol-45px me-4"><span class="symbol-label bg-warning-light"><i class="bi bi-list-task fs-2x text-warning"></i></span></span>
                        <div>
                            <div class="fw-bold text-gray-800">Queue</div>
                            <div class="text-muted fs-7">Messages waiting / sending</div>
                        </div>
                    </div>
                    <div class="fs-2x fw-bold text-gray-900" dir="ltr">{{ $pendingQueueCount }} / {{ $sendingQueueCount }}</div>
                    <div class="text-muted fs-8 mt-2">pending / sending</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Rate limiter on/off (audit 2026-08-21: the flag is now real).
         MUST be OUTSIDE the main form — nested forms are invalid HTML and the
         browser silently drops the inner one (that was the "toggle does nothing" bug). --}}
    <div class="alert alert-custom alert-light-{{ ($settings['enabled'] ?? true) ? 'success' : 'danger' }} d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-8 card" dir="rtl">
        <div>
            <div class="fw-bold fs-5">
                حماية التوقيت: {{ ($settings['enabled'] ?? true) ? 'مفعّلة' : 'متوقفة' }}
                <span class="badge badge-{{ ($settings['enabled'] ?? true) ? 'success' : 'danger' }} ms-2">{{ ($settings['enabled'] ?? true) ? 'ON' : 'OFF' }}</span>
            </div>
            <div class="text-muted fs-7 mt-1">عند الإيقاف: لا تأخير ولا حدود ساعية/يومية — الإرسال فوري. يُنصح بإبقائها مفعّلة لحماية الرقم.</div>
        </div>
        @if($canUpdateSafety)
        <form method="POST" action="{{ route('admin.whatsapp.safety.toggle_limiter') }}" class="m-0">
            @csrf
            <input type="hidden" name="enabled" value="{{ ($settings['enabled'] ?? true) ? '0' : '1' }}">
            <button type="submit" class="btn btn-sm btn-{{ ($settings['enabled'] ?? true) ? 'danger' : 'success' }}">
                {{ ($settings['enabled'] ?? true) ? 'إيقاف الحماية' : 'تفعيل الحماية' }}
            </button>
        </form>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.whatsapp.safety.update') }}" id="whatsapp-safety-form" class="card mb-8" dir="rtl">
        @csrf
        <div class="card-header border-0 pt-6">
            <div class="card-title d-flex flex-column align-items-start">
                <h3 class="fw-bold text-gray-900 mb-1">إعدادات التوقيت الآمن</h3>
                <span class="text-muted fs-7">غيّر سرعة الإرسال ضمن حدود تمنع القيم الخطرة وتحافظ على الرقم.</span>
            </div>
            <div class="card-toolbar">
                @if($canUpdateSafety)
                <button type="button" class="btn btn-light-warning" id="restore-balanced">
                    <i class="bi bi-arrow-counterclockwise"></i> استعادة الوضع المتوازن
                </button>
                @endif
            </div>
        </div>
        <div class="card-body pt-4">
            @php($selectedPreset = old('preset', $settings['preset'] ?? 'balanced'))
            <div class="row g-4 mb-7">
                <div class="col-lg-4">
                    <label class="card border border-2 h-100 p-5 cursor-pointer safety-preset-card" data-preset="very_safe">
                        <div class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input safety-preset" type="radio" name="preset" value="very_safe" {{ $selectedPreset === 'very_safe' ? 'checked' : '' }} {{ $canUpdateSafety ? '' : 'disabled' }}>
                            <span class="form-check-label fw-bold fs-5 me-2">آمن جدًا</span>
                        </div>
                        <span class="text-muted">للإرسال الكبير أو عندما تكون أولوية الحماية أعلى من السرعة.</span>
                    </label>
                </div>
                <div class="col-lg-4">
                    <label class="card border border-2 h-100 p-5 cursor-pointer safety-preset-card" data-preset="balanced">
                        <div class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input safety-preset" type="radio" name="preset" value="balanced" {{ $selectedPreset === 'balanced' ? 'checked' : '' }} {{ $canUpdateSafety ? '' : 'disabled' }}>
                            <span class="form-check-label fw-bold fs-5 me-2">متوازن — موصى به</span>
                        </div>
                        <span class="text-muted">الإعدادات التي نجحت في تجربة إيصالات الدفع الحية.</span>
                    </label>
                </div>
                <div class="col-lg-4">
                    <label class="card border border-2 h-100 p-5 cursor-pointer safety-preset-card" data-preset="custom">
                        <div class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input safety-preset" type="radio" name="preset" value="custom" {{ $selectedPreset === 'custom' ? 'checked' : '' }} {{ $canUpdateSafety ? '' : 'disabled' }}>
                            <span class="form-check-label fw-bold fs-5 me-2">مخصص</span>
                        </div>
                        <span class="text-muted">تحكم يدوي مع فرض الحدود الآمنة من الخادم.</span>
                    </label>
                </div>
            </div>

            <div class="alert alert-primary d-flex align-items-center mb-7">
                <i class="bi bi-stopwatch fs-2x me-4"></i>
                <div>
                    <div class="fw-bold">النطاق الفعلي المتوقع بين الرسائل</div>
                    <div class="fs-3 fw-bold" id="delay-preview" dir="ltr">—</div>
                    <div class="text-muted" id="pause-preview"></div>
                </div>
            </div>

            <div id="custom-safety-fields">
                <div class="row g-5">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">التأخير الأساسي — ثانية</label>
                        <input type="number" class="form-control safety-setting-input" name="base_delay" value="{{ old('base_delay', $settings['base_delay'] ?? 10) }}" min="{{ $safetyLimits['base_delay'][0] }}" max="{{ $safetyLimits['base_delay'][1] }}">
                        <div class="form-text">المسموح: {{ $safetyLimits['base_delay'][0] }}–{{ $safetyLimits['base_delay'][1] }} ثانية.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">التفاوت العشوائي ±%</label>
                        <input type="number" class="form-control safety-setting-input" name="jitter_percent" value="{{ old('jitter_percent', $settings['jitter_percent'] ?? 40) }}" min="{{ $safetyLimits['jitter_percent'][0] }}" max="{{ $safetyLimits['jitter_percent'][1] }}">
                        <div class="form-text">يمنع نمط الإرسال الثابت الذي يبدو آليًا.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">استراحة بعد كل — رسالة</label>
                        <input type="number" class="form-control safety-setting-input" name="batch_pause_every" value="{{ old('batch_pause_every', $settings['batch_pause_every'] ?? 25) }}" min="{{ $safetyLimits['batch_pause_every'][0] }}" max="{{ $safetyLimits['batch_pause_every'][1] }}">
                        <div class="form-text">المسموح: {{ $safetyLimits['batch_pause_every'][0] }}–{{ $safetyLimits['batch_pause_every'][1] }} رسالة.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">الحد الأقصى بالساعة</label>
                        <input type="number" class="form-control safety-setting-input" name="hourly_limit" value="{{ old('hourly_limit', $settings['hourly_limit'] ?? 60) }}" min="{{ $safetyLimits['hourly_limit'][0] }}" max="{{ $safetyLimits['hourly_limit'][1] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">الحد الأقصى باليوم</label>
                        <input type="number" class="form-control safety-setting-input" name="daily_limit" value="{{ old('daily_limit', $settings['daily_limit'] ?? 300) }}" min="{{ $safetyLimits['daily_limit'][0] }}" max="{{ $safetyLimits['daily_limit'][1] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">أقل استراحة — ثانية</label>
                        <input type="number" class="form-control safety-setting-input" name="batch_pause_min_seconds" value="{{ old('batch_pause_min_seconds', $settings['batch_pause_min_seconds'] ?? 180) }}" min="{{ $safetyLimits['batch_pause_seconds'][0] }}" max="{{ $safetyLimits['batch_pause_seconds'][1] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">أطول استراحة — ثانية</label>
                        <input type="number" class="form-control safety-setting-input" name="batch_pause_max_seconds" value="{{ old('batch_pause_max_seconds', $settings['batch_pause_max_seconds'] ?? 420) }}" min="{{ $safetyLimits['batch_pause_seconds'][0] }}" max="{{ $safetyLimits['batch_pause_seconds'][1] }}">
                    </div>
                </div>
            </div>

            <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-5 mt-7">
                <i class="bi bi-shield-lock fs-2x text-warning me-4"></i>
                <div class="text-gray-800">
                    <strong>الحماية إلزامية:</strong> لا يمكن تعطيل محدد السرعة أو تجاوز حد 60 رسالة بالساعة و300 رسالة باليوم. توقف الدفعات الطويل لا يحجز Worker؛ تبقى الرسائل Pending وتُستأنف تلقائيًا.
                </div>
            </div>

            @if($canUpdateSafety)
            <div class="d-flex justify-content-end mt-7">
                <button type="submit" class="btn btn-primary px-8">
                    <i class="bi bi-shield-check"></i> حفظ وتطبيق الإعدادات
                </button>
            </div>
            @else
            <div class="alert alert-secondary mt-7 mb-0">
                هذه الإعدادات للعرض فقط. يلزم امتلاك صلاحية تحديث إعدادات أمان WhatsApp لإجراء تغييرات.
            </div>
            @endif
        </div>
    </form>

    <div class="row g-5 g-xl-8">
        <div class="col-xl-6">
            <div class="card card-xl-stretch">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Safety Rules</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Current limiter configuration</span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gy-4">
                            <tbody>
                                <tr><td class="fw-semibold text-gray-700">Limiter enabled</td><td class="text-end"><span class="badge badge-{{ ($rateLimit['enabled'] ?? false) ? 'success' : 'danger' }}">{{ ($rateLimit['enabled'] ?? false) ? 'Yes' : 'No' }}</span></td></tr>
                                <tr><td class="fw-semibold text-gray-700">Hourly cap</td><td class="text-end fw-bold">{{ $settings['hourly_limit'] ?? 60 }}</td></tr>
                                <tr><td class="fw-semibold text-gray-700">Daily cap</td><td class="text-end fw-bold">{{ $settings['daily_limit'] ?? 300 }}</td></tr>
                                <tr><td class="fw-semibold text-gray-700">Batch pause every</td><td class="text-end fw-bold">{{ $settings['batch_pause_every'] ?? 25 }} messages</td></tr>
                                <tr><td class="fw-semibold text-gray-700">Batch pause duration</td><td class="text-end fw-bold" dir="ltr">{{ $settings['batch_pause_min_seconds'] ?? 180 }}–{{ $settings['batch_pause_max_seconds'] ?? 420 }}s</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4 mt-4">
                        <i class="bi bi-info-circle fs-2x text-primary me-4"></i>
                        <div class="fw-semibold text-gray-700">
                            If the limiter pauses sending, messages remain <strong>pending</strong> and continue later. They are not marked failed.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card card-xl-stretch">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Operational Snapshot</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Read-only health indicators</span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex justify-content-between border-bottom pb-3">
                            <span class="fw-semibold text-gray-700">Failed today</span>
                            <span class="fw-bold text-{{ $failedToday > 0 ? 'danger' : 'success' }}">{{ $failedToday }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-3">
                            <span class="fw-semibold text-gray-700">Last successful send</span>
                            <span class="fw-bold text-gray-800">{{ optional($lastSent)->updated_at ? $lastSent->updated_at->format('Y-m-d H:i') : 'None' }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-3">
                            <span class="fw-semibold text-gray-700">Last failed send</span>
                            <span class="fw-bold text-gray-800">{{ optional($lastFailed)->updated_at ? $lastFailed->updated_at->format('Y-m-d H:i') : 'None' }}</span>
                        </div>
                        @if(!empty($rateLimit['reason']))
                            <div class="alert alert-danger mb-0">
                                {{ $rateLimit['reason'] }}
                            </div>
                        @else
                            <div class="alert alert-success mb-0">
                                No safety pause is active right now.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-8">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-gray-900">آخر تغييرات إعدادات الحماية</span>
                <span class="text-muted mt-1 fw-semibold fs-7">سجل تدقيق يوضح من غيّر الإعدادات ومتى.</span>
            </h3>
        </div>
        <div class="card-body pt-0">
            @forelse($recentSafetyChanges as $change)
                @php($changedSettings = json_decode($change->new_data ?? '{}', true) ?: [])
                <div class="d-flex justify-content-between align-items-center border-bottom py-4">
                    <div>
                        <div class="fw-bold text-gray-800">{{ optional($change->user)->name ?? 'System' }}</div>
                        <div class="text-muted fs-8">{{ $change->ip_address ?? '—' }}</div>
                    </div>
                    <div class="text-center">
                        <span class="badge badge-light-primary">{{ $changedSettings['preset'] ?? 'custom' }}</span>
                    </div>
                    <div class="text-end text-muted">{{ optional($change->created_at)->format('Y-m-d H:i:s') }}</div>
                </div>
            @empty
                <div class="text-center text-muted py-7">لا توجد تغييرات مسجلة بعد.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const presets = {!! json_encode($safetyPresets, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
    const canUpdateSafety = {!! json_encode($canUpdateSafety) !!};
    const fieldNames = [
        'base_delay', 'jitter_percent', 'hourly_limit', 'daily_limit',
        'batch_pause_every', 'batch_pause_min_seconds', 'batch_pause_max_seconds'
    ];
    const radios = document.querySelectorAll('.safety-preset');
    const fields = document.querySelectorAll('.safety-setting-input');

    function selectedPreset() {
        return document.querySelector('.safety-preset:checked').value;
    }

    function fieldValue(name) {
        return Number(document.querySelector(`[name="${name}"]`).value || 0);
    }

    function updatePreview() {
        const base = fieldValue('base_delay');
        const jitter = fieldValue('jitter_percent');
        const jitterSeconds = Math.round(base * (jitter / 100));
        const minimum = Math.max(0, base - jitterSeconds);
        const maximum = base + jitterSeconds;
        const pauseEvery = fieldValue('batch_pause_every');
        const pauseMin = fieldValue('batch_pause_min_seconds') / 60;
        const pauseMax = fieldValue('batch_pause_max_seconds') / 60;

        document.getElementById('delay-preview').textContent = `${minimum}–${maximum} ثانية`;
        document.getElementById('pause-preview').textContent = `استراحة بعد كل ${pauseEvery} رسالة لمدة ${pauseMin.toFixed(1)}–${pauseMax.toFixed(1)} دقيقة`;
    }

    function applyPreset(preset) {
        if (preset !== 'custom') {
            fieldNames.forEach(function (name) {
                document.querySelector(`[name="${name}"]`).value = presets[preset][name];
            });
        }

        fields.forEach(function (field) {
            field.disabled = !canUpdateSafety || preset !== 'custom';
        });
        document.getElementById('custom-safety-fields').classList.toggle('opacity-50', !canUpdateSafety || preset !== 'custom');
        document.querySelectorAll('.safety-preset-card').forEach(function (card) {
            card.classList.toggle('border-primary', card.dataset.preset === preset);
        });
        updatePreview();
    }

    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            applyPreset(radio.value);
        });
    });
    fields.forEach(function (field) {
        field.addEventListener('input', updatePreview);
    });
    document.getElementById('restore-balanced')?.addEventListener('click', function () {
        const balanced = document.querySelector('.safety-preset[value="balanced"]');
        balanced.checked = true;
        applyPreset('balanced');
    });

    applyPreset(selectedPreset());
});
</script>
@endsection
