@extends('dashbord.layouts.master')

@section('css')
    <style>
        .bonus-card { border-top: 3px solid #17a2b8; }
        .month-box { border-radius: 10px; padding: 8px 6px; text-align: center; font-size: 12px; border: 1px solid #e4e6ef; }
        .month-on_time { background: #d4f5e2; border-color: #7ed6a5; }
        .month-catchup { background: #fff3cd; border-color: #ffd666; }
        .month-late    { background: #fde0e0; border-color: #f5a3a3; }
        .month-unpaid  { background: #f3f4f6; border-color: #dcdfe4; }
        .month-box .m { font-weight: 700; display: block; }
        .month-box .s { font-size: 11px; color: #555; display: block; }
        .month-box .d { font-size: 10px; color: #8a9099; display: block; margin-top: 2px; direction: ltr; }
        .st-ok { background: #d4f5e2; color: #0f7b45; }
        .st-catchup { background: #fff3cd; color: #8a6d00; }
        .st-bad { background: #fde0e0; color: #a31919; }
    </style>
@endsection

@section('content')
    <div class="app-content flex-column-fluid" style="padding-top: 20px">
        <div class="container-fluid">

            <div class="mb-3 d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.bonus.index') }}" class="btn btn-sm btn-light">&larr; رجوع للقائمة</a>
                <a href="{{ route('admin.bonus.log') }}" class="btn btn-sm btn-light-primary">سجل البونص</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success py-2" style="font-size:13px">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger py-2" style="font-size:13px">{{ session('error') }}</div>
            @endif

            <div class="card shadow-sm bonus-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0">{{ $client->name }}</h3>
                        <span class="text-muted" style="font-size:13px">{{ $client->phone }}</span>
                    </div>
                    <div class="text-end">
                        <div style="font-size:26px;font-weight:700" dir="ltr">{{ $progress->credits }} / {{ $progress->required }}</div>
                        <div class="text-muted" style="font-size:12px">الأشهر المحتسبة</div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-2 g-md-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 p-md-3 h-100">
                                <div class="text-muted" style="font-size:12px">الحالة</div>
                                <div class="fw-bold" style="font-size:15px">{{ $progress->statusLabel() }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 p-md-3 h-100">
                                <div class="text-muted" style="font-size:12px">الرصيد الحالي</div>
                                <div class="fw-bold" style="font-size:15px" dir="ltr">{{ number_format($progress->balance, 2) }}$</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 p-md-3 h-100">
                                <div class="text-muted" style="font-size:12px">باقي للاستحقاق</div>
                                <div class="fw-bold" style="font-size:15px" dir="ltr">{{ $progress->remainingToEarn() }} شهر</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 p-md-3 h-100">
                                <div class="text-muted" style="font-size:12px">الشهر المتوقع</div>
                                <div class="fw-bold" style="font-size:13px" dir="ltr">{{ $progress->nextEligibleMonth ?? '—' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" style="font-size:13px">
                        <span class="text-muted">حالة البونص:</span>
                        @if($openBonus)
                            @switch($openBonus->status)
                                @case('pending') <b>بانتظار القرار</b> @break
                                @case('approved') <b>معتمد (لم يُصدر)</b> @break
                                @case('postponed') <b class="text-warning">⏸ مؤجّل</b> @break
                                @default <b>{{ $openBonus->status }}</b>
                            @endswitch
                        @else
                            <b>لا يوجد بونص مفتوح</b>
                        @endif
                    </div>

                    @if($progress->balance > 0.009)
                        <div class="alert alert-warning py-2 mb-3" style="font-size:12px">
                            الرصيد غير صفري: يجب تصفيته قبل إصدار الفاتورة.
                        </div>
                    @endif

                    <div class="alert alert-light border py-2 mb-3" style="font-size:12px">
                        <b>القاعدة:</b> الشهر يُحتسب إذا كان رصيد الزبون صفراً عند استحقاق الفاتورة التالية
                        + أيام السماح. العدّ تراكمي — الشهر غير المسدَّد لا يُحتسب فقط، ولا يُصفَّر شيء.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size:12px">
                            <thead>
                                <tr>
                                    <th>الشهر</th>
                                    <th>الاستحقاق</th>
                                    <th>الحالة</th>
                                    <th>الرصيد عند الحدّ</th>
                                    <th>تاريخ الدفع</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($progress->months as $m)
                                <tr>
                                    <td class="fw-bold">{{ $m['month'] }}</td>
                                    <td>{{ $m['due'] }}</td>
                                    <td>
                                        @if($m['credited'])
                                            <span class="badge st-ok">✓ محتسب</span>
                                        @else
                                            <span class="badge st-bad">غير محتسب</span>
                                        @endif
                                    </td>
                                    <td dir="ltr" class="text-end">{{ number_format($m['balance'], 2) }}$</td>
                                    <td dir="ltr" class="text-end">
                                        {{ !empty($m['paid_at']) ? \Illuminate\Support\Carbon::parse($m['paid_at'])->format('Y-m-d H:i') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">لا توجد أشهر محسوبة بعد</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h6 class="mt-4 mb-2 fw-bold">إجراءات الإدارة</h6>

                    {{-- ========== الإجراءات الأساسية ========== --}}
                    <div class="row g-2 mb-3">
                        @if(! $progress->hasOpenBonus)
                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 h-100 d-flex flex-column">
                                    <div class="fw-bold mb-1" style="font-size:13.5px">🎁 منح شهر مجاني</div>
                                    <div class="text-muted mb-2" style="font-size:11.5px">
                                        @if($eligible)
                                            الزبون مستحق — المنح يُنشئ التزاماً مفتوحاً يحتاج اعتماداً.
                                        @else
                                            غير مستحق بعد ({{ $progress->credits }}/{{ $progress->required }}).
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('admin.bonus.grant', $client->id) }}" class="mt-auto js-confirm"
                                          data-title="منح شهر مجاني؟"
                                          data-text="سيُسجَّل بونص لهذا الزبون بحالة «معلّق»، ثم تعتمده أو تُلغيه."
                                          data-confirm="نعم، امنح">
                                        @csrf
                                        <input type="hidden" name="approve" value="0">
                                        <input type="hidden" name="notes" value="منح يدوي من شاشة البونص">
                                        <button class="btn btn-light-success border border-success w-100 btn-sm fw-bold">تسجيل البونص (معلّق)</button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        @if($openBonus && $openBonus->status === 'pending')
                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 h-100 d-flex flex-column">
                                    <div class="fw-bold mb-1" style="font-size:13.5px">✔ اعتماد البونص</div>
                                    <div class="text-muted mb-2" style="font-size:11.5px">
                                        بونص #{{ $openBonus->id }} معلّق منذ {{ substr((string) $openBonus->earned_at, 0, 10) }}
                                    </div>
                                    <form method="POST" action="{{ route('admin.bonus.approve', $openBonus->id) }}" class="mt-auto js-confirm"
                                          data-title="اعتماد البونص؟"
                                          data-text="سيُسجَّل اعتماد الإدارة. لا تُنشأ أي فاتورة — الإصدار زر منفصل."
                                          data-confirm="نعم، اعتمد">
                                        @csrf
                                        <button class="btn btn-primary w-100 btn-sm">اعتماد البونص #{{ $openBonus->id }}</button>
                                    </form>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 h-100 d-flex flex-column" style="border-color:#f5c2c7!important">
                                    <div class="fw-bold mb-1" style="font-size:13.5px">🗑 حذف البونص المعلّق</div>
                                    <div class="text-muted mb-2" style="font-size:11.5px">
                                        للضغط الخاطئ: يُحذف السجل نهائياً ولا يترك أثراً على الفواتير أو الرصيد.
                                    </div>
                                    <form method="POST" action="{{ route('admin.bonus.delete', $openBonus->id) }}" class="mt-auto js-confirm"
                                          data-title="حذف البونص #{{ $openBonus->id }} نهائياً؟"
                                          data-text="سيُحذف السجل من القائمة. لا يمكن التراجع."
                                          data-confirm="نعم، احذف"
                                          data-danger="1">
                                        @csrf
                                        <input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="سبب الحذف (اختياري)" maxlength="255">
                                        <button class="btn btn-light-danger border border-danger w-100 btn-sm fw-bold">حذف نهائي</button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        @if($openBonus && in_array($openBonus->status, ['pending', 'approved', 'postponed'], true))
                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 h-100 d-flex flex-column">
                                    <div class="fw-bold mb-1" style="font-size:13.5px">⏸ تأجيل الشهر المجاني</div>
                                    <div class="text-muted mb-2" style="font-size:11.5px">
                                        يبقى الحق محفوظاً ولا تُنشأ أي فاتورة. استخدمه عندما يسمح وضع الشركة.
                                    </div>
                                    <form method="POST" action="{{ route('admin.bonus.postpone', $openBonus->id) }}" class="mt-auto js-confirm"
                                          data-title="تأجيل الشهر المجاني؟"
                                          data-text="سيبقى الحق محفوظاً لهذا الزبون، ولن تُنشأ أي فاتورة الآن."
                                          data-confirm="نعم، أجّل">
                                        @csrf
                                        <input type="text" name="reason" class="form-control form-control-sm mb-2"
                                               placeholder="سبب التأجيل (مطلوب)" maxlength="255" required
                                               value="{{ $openBonus->status === 'postponed' ? $openBonus->postpone_reason : '' }}">
                                        <input type="month" name="target_month" class="form-control form-control-sm mb-2"
                                               value="{{ $openBonus->postpone_target_month }}" title="الشهر المستهدف (اختياري)">
                                        <button class="btn btn-light-warning border border-warning w-100 btn-sm fw-bold">{{ $openBonus->status === 'postponed' ? 'تحديث التأجيل' : 'تأجيل' }}</button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        @if($openBonus && in_array($openBonus->status, ['approved', 'postponed'], true))
                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 h-100 d-flex flex-column">
                                    <div class="fw-bold mb-1" style="font-size:13.5px">🧾 إصدار فاتورة الشهر المجاني</div>
                                    <div class="text-muted mb-2" style="font-size:11.5px">
                                        @if($balance > 0)
                                            معطّل: عليه {{ number_format($balance, 2) }}$ — تُصفّى أولاً.
                                        @else
                                            ستُنشأ فاتورة 0$ بتاريخ الاستحقاق الطبيعي للدورة القادمة.
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('admin.bonus.apply', $openBonus->id) }}" class="mt-auto js-confirm"
                                          data-title="إصدار فاتورة الشهر المجاني؟"
                                          data-text="ستُنشأ فاتورة بقيمة 0$ وتُسجَّل مُستبدلة. لا يمكن التراجع من هنا."
                                          data-confirm="نعم، أصدِر">
                                        @csrf
                                        <button class="btn {{ $balance > 0 ? 'btn-light-secondary' : 'btn-success' }} w-100 btn-sm"
                                                {{ $balance > 0 ? 'disabled' : '' }}>
                                            إصدار الفاتورة #{{ $openBonus->id }}
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 h-100 d-flex flex-column">
                                    <div class="fw-bold mb-1" style="font-size:13.5px">✖ إلغاء البونص</div>
                                    <div class="text-muted mb-2" style="font-size:11.5px">يُحذف الالتزام ويُسجَّل السبب في السجل.</div>
                                    <form method="POST" action="{{ route('admin.bonus.cancel', $openBonus->id) }}" class="mt-auto js-confirm"
                                          data-title="إلغاء البونص #{{ $openBonus->id }}؟"
                                          data-text="سيُلغى البونص نهائياً ويُسجَّل الإلغاء باسمك."
                                          data-confirm="نعم، ألغِ"
                                          data-danger="1">
                                        @csrf
                                        <input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="سبب الإلغاء (اختياري)">
                                        <button class="btn btn-light-danger border border-danger w-100 btn-sm fw-bold">إلغاء البونص</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- هاتف: بطاقات --}}
                    <div class="d-md-none">
                        @forelse($bonuses as $b)
                            <div class="border rounded p-2 mb-2" style="font-size:12px">
                                <div class="d-flex justify-content-between">
                                    <strong>بونص #{{ $b->id }}</strong>
                                    <span>{{ $statusLabels[$b->status] ?? $b->status }}</span>
                                </div>
                                <div class="text-muted">
                                    استُحق: {{ substr((string) $b->earned_at, 0, 10) }}
                                    · المصدر: {{ $sourceLabels[$b->source] ?? $b->source }}
                                </div>
                                @if($b->approved_by_name)
                                    <div class="text-muted">اعتمده: {{ $b->approved_by_name }}</div>
                                @endif
                                @if($b->redeemed_invoice_id)
                                    <a href="{{ route('admin.invoice_details', $b->redeemed_invoice_id) }}" class="d-block mt-1">فاتورة #{{ $b->redeemed_invoice_id }} ←</a>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-3" style="font-size:12.5px">لا يوجد سجل بعد</div>
                        @endforelse
                    </div>

                    {{-- كمبيوتر: جدول --}}
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-sm" style="font-size:12px">
                            <thead><tr>
                                <th>#</th><th>تاريخ الاستحقاق</th><th>الحالة</th><th>المصدر</th>
                                <th>اعتمده</th><th>تاريخ الاستبدال</th><th>الفاتورة</th>
                            </tr></thead>
                            <tbody>
                            @forelse($bonuses as $b)
                                <tr>
                                    <td>{{ $b->id }}</td>
                                    <td>{{ substr((string) $b->earned_at, 0, 10) }}</td>
                                    <td>
                                        <span class="badge {{ $b->status === 'redeemed' ? 'badge-soft-success' : ($b->status === 'cancelled' ? 'badge-soft-danger' : 'badge-soft-warning') }}">
                                            {{ $statusLabels[$b->status] ?? $b->status }}
                                        </span>
                                    </td>
                                    <td>{{ $sourceLabels[$b->source] ?? $b->source }}</td>
                                    <td>{{ $b->approved_by_name ?? '—' }}</td>
                                    <td>{{ $b->redeemed_at ? substr((string) $b->redeemed_at, 0, 10) : '—' }}</td>
                                    <td>
                                        @if($b->redeemed_invoice_id)
                                            <a href="{{ route('admin.invoice_details', $b->redeemed_invoice_id) }}">#{{ $b->redeemed_invoice_id }}</a>
                                        @else — @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">لا يوجد سجل بعد</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 mb-0" style="font-size:12.5px;background:#f4f6f8;border:1px solid #e2e6ea;border-radius:10px;padding:10px 12px;color:#4d5863">
                        التقدّم محسوب من الفواتير والدفعات مباشرة ولا يُخزَّن، لذلك يبقى مطابقاً بعد أي استرجاع أو تعديل دفع.
                    </div>

                    <script>
                        document.querySelectorAll('form.js-confirm').forEach(function (form) {
                            form.addEventListener('submit', function (e) {
                                if (form.dataset.confirmed === '1') return;
                                e.preventDefault();

                                var title = form.dataset.title || 'تأكيد';
                                var text  = form.dataset.text || '';
                                var okTxt = form.dataset.confirm || 'نعم';
                                var danger = form.dataset.danger === '1';

                                if (typeof Swal === 'undefined') {
                                    if (window.confirm(title + '\n' + text)) { form.dataset.confirmed = '1'; form.submit(); }
                                    return;
                                }

                                Swal.fire({
                                    title: title,
                                    text: text,
                                    icon: danger ? 'warning' : 'question',
                                    showCancelButton: true,
                                    confirmButtonText: okTxt,
                                    cancelButtonText: 'إلغاء',
                                    confirmButtonColor: danger ? '#f1416c' : '#3e97ff'
                                }).then(function (res) {
                                    if (res.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); }
                                });
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
@endsection
