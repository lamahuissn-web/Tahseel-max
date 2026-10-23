@extends('dashbord.layouts.master')

@section('content')
    <div class="app-content flex-column-fluid" style="padding-top: 14px">
        <div class="container-fluid px-2 px-md-4">
            <div class="mb-3">
                <a href="{{ route('admin.bonus.index') }}" class="btn btn-sm btn-light">&larr; رجوع</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card shadow-sm" style="border-top: 3px solid #0d6efd">
                <div class="card-header">
                    <h3 class="card-title mb-0">إعدادات البونص</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.bonus.settings.save') }}">
                        @csrf
                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label fw-bold">تفعيل البرنامج</label>
                                <select name="bonus_enabled" class="form-select">
                                    <option value="1" {{ ($settings['bonus_enabled'] ?? '0') === '1' ? 'selected' : '' }}>مفعّل</option>
                                    <option value="0" {{ ($settings['bonus_enabled'] ?? '0') !== '1' ? 'selected' : '' }}>متوقف</option>
                                </select>
                                <div class="form-text">عند الإيقاف يتوقف الحساب دون حذف أي بيانات.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">عدد الأشهر المطلوبة</label>
                                <input type="number" min="1" max="120" name="bonus_months_required" class="form-control"
                                       value="{{ $settings['bonus_months_required'] ?? 24 }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">فترة السماح (أيام إضافية)</label>
                                <input type="number" min="0" max="31" name="bonus_grace_days" class="form-control"
                                       value="{{ $settings['bonus_grace_days'] ?? 0 }}">
                                <div class="form-text">0 = القاعدة الأساسية فقط (الدفع داخل الشهر).</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">أشهر نظيفة للعودة إلى «منتظم»</label>
                                <input type="number" min="1" max="12" name="bonus_recovery_months" class="form-control"
                                       value="{{ $settings['bonus_recovery_months'] ?? 3 }}">
                                <div class="form-text">بعد أي كسر: كم شهر دفع منتظم يحتاج ليصبح 🟢 منتظم. (3 = الموصى به)</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">عدد الاستدراكات في الدورة</label>
                                <input type="number" min="0" max="12" name="bonus_catchup_per_cycle" class="form-control"
                                       value="{{ $settings['bonus_catchup_per_cycle'] ?? 1 }}">
                                <div class="form-text">0 = بدون استدراك (صارم).</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">أكثر من كم شهر كسر ⇒ تصفير</label>
                                <input type="number" min="0" max="12" name="bonus_hard_reset_after_months" class="form-control"
                                       value="{{ $settings['bonus_hard_reset_after_months'] ?? 2 }}">
                                <div class="form-text">2 = كسر أكثر من شهرين يصفر العدّاد.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">حدود الدوامة</label>
                                <input type="number" min="1" max="12" name="bonus_spiral_after_months" class="form-control"
                                       value="{{ $settings['bonus_spiral_after_months'] ?? 2 }}">
                                <div class="form-text">عدد حدود الأشهر المتتالية بكسر قديم ⇒ دوامة.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">أنواع الزبائن المشمولين</label>
                                <select name="bonus_client_types" class="form-select">
                                    <option value="internet" {{ str_contains($settings['bonus_client_types'] ?? 'internet', 'satellite') ? '' : 'selected' }}>
                                        الإنترنت فقط (الستلايت غير مشمول)
                                    </option>
                                    <option value="both" {{ str_contains($settings['bonus_client_types'] ?? 'internet', 'satellite') ? 'selected' : '' }}>
                                        الإنترنت والستلايت
                                    </option>
                                </select>
                                <div class="form-text">الستلايت مستثنى من برنامج البونص.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">تاريخ بدء الاحتساب</label>
                                <input type="date" name="bonus_count_start_date" class="form-control"
                                       value="{{ $settings['bonus_count_start_date'] ?? '2026-01-01' }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">شرط الرصيد الصفري للاستبدال</label>
                                <select name="bonus_require_zero_balance" class="form-select">
                                    <option value="1" {{ ($settings['bonus_require_zero_balance'] ?? '1') === '1' ? 'selected' : '' }}>نعم (لا استبدال مع وجود دين)</option>
                                    <option value="0" {{ ($settings['bonus_require_zero_balance'] ?? '1') !== '1' ? 'selected' : '' }}>لا</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">إصدار الفاتورة</label>
                                <div class="form-control bg-light" style="font-size:12.5px">يدوي دائماً — بقرار الإدارة</div>
                                <div class="text-muted mt-1" style="font-size:11px">لا يصدر النظام أي فاتورة شهر مجاني تلقائياً.</div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">وصف فاتورة الشهر المجاني</label>
                                <input type="text" name="bonus_invoice_notes" class="form-control"
                                       value="{{ $settings['bonus_invoice_notes'] ?? '' }}">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary w-100" style="max-width:420px">حفظ الإعدادات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
