@extends('dashbord.layouts.master')

@section('content')
    <div class="app-content flex-column-fluid" style="padding-top: 14px">
        <div class="container-fluid px-2 px-md-4">

            <div class="mb-3">
                <a href="{{ route('admin.bonus.index') }}" class="btn btn-sm btn-light">&larr; رجوع للقائمة</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @php($pending = session('pendingSave') ?? null)

            @if($preview && $preview['count'] > 0)
                <div class="alert alert-warning">
                    <div class="fw-bold mb-1">قبل الحفظ — هذا التغيير يفتح استحقاقاً فورياً</div>
                    <div>
                        بهذه القيم يصبح مستحقاً فوراً:
                        <b>{{ $preview['count'] }}</b> عميلاً — الكلفة:
                        <b>${{ number_format($preview['cost'], 2) }}</b>
                    </div>
                    <div class="small mt-1">لن يُحفظ شيء قبل تأكيدك.</div>

                    <form method="POST" action="{{ route('admin.bonus.settings.save') }}" class="mt-3">
                        @csrf
                        <input type="hidden" name="confirmed" value="1">
                        <input type="hidden" name="bonus_months_required" value="{{ $months }}">
                        <input type="hidden" name="bonus_grace_days" value="{{ $graceDays }}">
                        <input type="hidden" name="bonus_count_start_date" value="{{ $startDate }}">
                        <input type="hidden" name="bonus_enabled" value="{{ $enabled ? '1' : '0' }}">
                        <button class="btn btn-warning">أؤكد الحفظ رغم الكلفة</button>
                        <a href="{{ route('admin.bonus.settings') }}" class="btn btn-light">إلغاء</a>
                    </form>
                </div>
            @endif

            <div class="card shadow-sm" style="border-top: 3px solid #0d6efd">
                <div class="card-header bg-white">
                    <h5 class="mb-0">⚙️ إعدادات البونص</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.bonus.settings.save') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">عدد الأشهر المطلوبة</label>
                                <input type="number" name="bonus_months_required" class="form-control"
                                       min="1" max="120" value="{{ old('bonus_months_required', $months) }}" required>
                                <div class="form-text">12 شهراً أو 24 — القرار لك. العتبة تُقارن بالعدّاد.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">أيام السماح</label>
                                <input type="number" name="bonus_grace_days" class="form-control"
                                       min="0" max="31" value="{{ old('bonus_grace_days', $graceDays) }}" required>
                                <div class="form-text">تُضاف بعد استحقاق الفاتورة التالية قبل الحكم على الشهر.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">تاريخ بداية العدّ</label>
                                <input type="date" name="bonus_count_start_date" class="form-control"
                                       value="{{ old('bonus_count_start_date', $startDate) }}" required>
                                <div class="form-text">لا يُحتسب أي شهر قبل هذا التاريخ.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold d-block">حالة البرنامج</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="bonus_enabled" value="1"
                                           id="bonus_enabled" @checked(old('bonus_enabled', $enabled))>
                                    <label class="form-check-label" for="bonus_enabled">
                                        مفعّل (بدونه تتوقف المزامنة الليلية)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row g-2 text-muted small">
                            <div class="col-12 col-md-6">
                                قيمة فاتورة الشهر المجاني: <b>{{ number_format($invoiceValue, 2) }}$</b>
                                (يجب أن تبقى صفراً — والخادم يرفض الإصدار بغير ذلك).
                            </div>
                            <div class="col-12 col-md-6">
                                بالقيم المحفوظة الآن: مستحقون
                                <b>{{ $current['count'] }}</b> عميلاً — كلفة
                                <b>${{ number_format($current['cost'], 2) }}</b>.
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary">حفظ</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-body small text-muted">
                    <b>القاعدة الوحيدة:</b> شهر يُحتسب إذا كان رصيد الزبون الكلي صفراً عند استحقاق
                    الفاتورة التالية + أيام السماح (فواتير الخدمات محسوبة).
                    العدّ تراكمي: الشهر غير المسدَّد لا يُحتسب فقط — ولا يُصفَّر شيء.
                </div>
            </div>

        </div>
    </div>
@endsection
