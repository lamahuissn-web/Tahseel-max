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
                        <div style="font-size:26px;font-weight:700" dir="ltr">{{ $progress->progress }} / {{ $progress->required }}</div>
                        <div class="text-muted" style="font-size:12px">تقدّم الدورة</div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-2 g-md-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 p-md-3 h-100">
                                <div class="text-muted" style="font-size:12px">الحالة</div>
                                <div class="fw-bold" style="font-size:13px">
                                    @switch($progress->status)
                                        @case('compliant') 🟢 منتظم @break
                                        @case('recovering') 🟡 عائد للانتظام @break
                                        @case('excellent') 💎 ممتاز في الدفع @break
                                        @case('late') 🟠 متأخر @break
                                        @case('spiral') 🔴 دوامة @break
                                        @case('hard_reset') ⚫ متعثر (العداد صفر) @break
                                        @case('excluded') ⛔ غير مشمول بالبرنامج @break
                                        @case('inactive') ⛔ غير نشط — خارج البرنامج @break
                                        @default {{ $progress->status }}
                                    @endswitch
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 p-md-3 h-100">
                                <div class="text-muted" style="font-size:12px">الشهر المتوقع</div>
                                <div class="fw-bold" style="font-size:13px">{{ $progress->nextEligibleMonth ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 p-md-3 h-100">
                                <div class="text-muted" style="font-size:12px">استدراكات مستخدمة</div>
                                <div class="fw-bold" style="font-size:13px">{{ $progress->catchupsUsed }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 p-md-3 h-100">
                                <div class="text-muted" style="font-size:12px">أشهر متأخرة (مستحقة)</div>
                                <div class="fw-bold" style="font-size:13px">{{ $progress->carriedArrears }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mb-4" style="font-size:13px">
                        <div class="fw-bold mb-2">⏳ ما تبقّى قبل تغيّر التصنيف</div>
                        @if($progress->catchupsUsed > 0)
                            <div class="mb-2">
                                🟡 <strong>الاستدراك</strong>:
                                @if($progress->lastCatchupMonth)
                                    استُخدم في <strong dir="ltr">{{ $progress->lastCatchupMonth }}</strong>
                                @else
                                    استُخدم
                                @endif
                                — لا يوجد رصيد استدراك متبقٍ في هذه الدورة
                                <span class="text-muted">(أشهر نظيفة متتالية: {{ $progress->cleanMonths }} من {{ \App\Services\Bonus\BonusSettings::recoveryMonths() }})</span>
                            </div>
                        @endif
                        <div class="d-flex flex-wrap gap-3">
                            <div>
                                🔴 ليصبح <strong>دوامة</strong>:
                                @if($progress->monthsToSpiral > 0)
                                    <strong>{{ $progress->monthsToSpiral }}</strong> شهر متأخر إضافي
                                @else
                                    <strong>هو الآن في حالة دوامة</strong>
                                @endif
                            </div>
                            <div>
                                ⚫ ليصبح <strong>متعثر</strong> (العدّاد صفر):
                                @if($progress->monthsToHardReset > 0)
                                    <strong>{{ $progress->monthsToHardReset }}</strong> شهر متأخر إضافي
                                @else
                                    <strong>هو الآن متعثر</strong>
                                @endif
                            </div>
                            @if($progress->nextBoundaryDate)
                                <div>
                                    📅 التاريخ الحاسم:
                                    <strong dir="ltr">{{ $progress->nextBoundaryDate }}</strong>
                                    @if($progress->daysToNextBoundary !== null)
                                        (بعد <strong>{{ $progress->daysToNextBoundary }}</strong> يوم)
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ===== نمط الدفع ودرجة الالتزام ===== --}}
                    @php
                        $patMap = [
                            'precise'    => ['⚡ دقيق', 'يدفع قبل أو في تاريخ الاستحقاق', '#0c6a4a', '#d8f5ea'],
                            'week'       => ['✅ خلال أسبوع', 'يدفع خلال الأيام السبعة الأولى', '#1c6b33', '#dff3e3'],
                            'late_habit' => ['🕐 متأخر عادةً', 'يدفع عادةً بعد 8–15 يوماً', '#8a6200', '#fff2d1'],
                            'slow'       => ['🐢 مماطل', 'يدفع عادةً بعد 16–30 يوماً', '#8a4b00', '#ffe6cc'],
                            'very_slow'  => ['⏳ مماطل جداً', 'يدفع عادةً بعد أكثر من 30 يوماً', '#a31919', '#fde0e0'],
                        ];
                        $pat = $patMap[$progress->pattern] ?? ['— لا سجل كافٍ', 'لا توجد دفعات كافية بعد', '#7a838c', '#eef0f2'];
                        $scoreColor = $progress->score >= 85 ? '#1c6b33' : ($progress->score >= 60 ? '#8a6200' : '#a31919');
                    @endphp
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <div style="border:1px solid #e9ecef;border-radius:12px;padding:12px">
                                <div class="text-muted mb-1" style="font-size:12px">نمط الدفع (كيف يدفع عادةً)</div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="pat-badge" style="background:{{ $pat[3] }};color:{{ $pat[2] }};font-size:13px;padding:4px 10px">{{ $pat[0] }}</span>
                                    <span class="fw-bold">{{ $progress->lagLabel() }}</span>
                                </div>
                                <div class="text-muted mt-1" style="font-size:12px">{{ $pat[1] }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div style="border:1px solid #e9ecef;border-radius:12px;padding:12px">
                                <div class="text-muted mb-1" style="font-size:12px">درجة الالتزام (0–100)</div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold" style="font-size:26px;color:{{ $scoreColor }}" dir="ltr">{{ $progress->score }}</span>
                                    <div class="flex-grow-1">
                                        <div class="progress" style="height:10px">
                                            <div class="progress-bar" style="width:{{ $progress->score }}%;background:{{ $scoreColor }}"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-muted mt-1" style="font-size:11.5px">
                                    45% الموقف المالي · 35% الأشهر المحتسبة · 20% الانتظام
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="mb-2" style="font-size:15px">سجل الأشهر</h4>
                    <div class="row g-2 mb-3">
                        @foreach($progress->months as $m)
                            @php
                                $isCatchup = ($m['state'] ?? '') === 'catchup';
                                $cls = $isCatchup
                                    ? 'month-catchup'
                                    : (($m['state'] ?? '') === 'on_time' ? 'month-on_time' : (($m['state'] ?? '') === 'late' ? 'month-late' : 'month-unpaid'));
                            @endphp
                            <div class="col-6 col-md-3">
                                <div class="month-box {{ $cls }}">
                                    <span class="m">{{ $m['month'] }}</span>
                                    <span class="s">
                                        @if($isCatchup && $m['credited']) ✓ استدراك
                                        @elseif($m['credited']) ✓ محتسب
                                        @elseif(($m['state'] ?? '') === 'duplicate') ✗ شهر مكرر
                                        @elseif(($m['state'] ?? '') === 'unpaid') — غير مدفوع
                                        @else ✗ غير محتسب
                                        @endif
                                    </span>
                                    <span class="d">
                                        @if(!empty($m['paid_at']))
                                            دُفع: {{ \Illuminate\Support\Carbon::parse($m['paid_at'])->format('Y-m-d H:i') }}
                                        @else
                                            استحقاق: {{ $m['due'] }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                        @if(empty($progress->months))
                            <div class="col-12 text-muted">لا توجد فواتير اشتراك بعد تاريخ بدء الاحتساب.</div>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size:12px">
                            <thead>
                                <tr>
                                    <th>الشهر</th>
                                    <th>الاستحقاق</th>
                                    <th>الحالة</th>
                                    <th>تاريخ ووقت الدفع</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($progress->months as $m)
                                <tr>
                                    <td class="fw-bold">{{ $m['month'] }}</td>
                                    <td>{{ $m['due'] }}</td>
                                    <td>
                                        @if(($m['state'] ?? '') === 'catchup') <span class="badge st-catchup">🟡 استدراك</span>
                                        @elseif($m['credited']) <span class="badge st-ok">✓ محتسب</span>
                                        @elseif(($m['state'] ?? '') === 'duplicate') <span class="badge st-bad">شهر مكرر</span>
                                        @elseif(($m['state'] ?? '') === 'unpaid') <span class="badge st-bad">غير مدفوع</span>
                                        @else <span class="badge st-bad">غير محتسب</span>
                                        @endif
                                    </td>
                                    <td dir="ltr" class="text-end">
                                        {{ !empty($m['paid_at']) ? \Illuminate\Support\Carbon::parse($m['paid_at'])->format('Y-m-d H:i') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">لا توجد أشهر</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-4">

                    <h4 class="mb-3" style="font-size:15px">إجراءات الإدارة</h4>

                    @php
                        $statusLabels = ['pending' => 'معلّق', 'approved' => 'معتمد', 'redeemed' => 'مُستبدل', 'cancelled' => 'ملغى'];
                        $sourceLabels = ['auto' => 'تلقائي (24 شهر)', 'manual' => 'يدوي'];
                        $canApply = $openBonus && $openBonus->status === 'approved' && $balance <= 0;
                    @endphp

                    {{-- ========== صندوق الحالة ========== --}}
                    <div class="border rounded p-3 mb-3" style="background:#fbfcfd">
                        <div class="fw-bold mb-2" style="font-size:13.5px">حالة البونص الآن</div>
                        <div class="row g-2" style="font-size:12.5px">
                            <div class="col-12 col-md-6">
                                @if($progress->progress >= $progress->required)
                                    <div class="text-success">✔ بلغ شرط الأشهر ({{ $progress->progress }}/{{ $progress->required }})</div>
                                @else
                                    <div class="text-muted">⏳ التقدّم {{ $progress->progress }}/{{ $progress->required }} — يتبقّى <strong>{{ $remainingToEarn }}</strong> شهراً</div>
                                @endif
                                @if($progress->hasOpenBonus)
                                    <div class="text-warning">🎁 يوجد بونص مفتوح — لا يمكن منح آخر قبل إغلاقه (يمكن حذفه إن كان بالخطأ)</div>
                                @else
                                    <div class="text-muted">🎁 لا يوجد بونص مفتوح</div>
                                @endif
                            </div>
                            <div class="col-12 col-md-6">
                                @if($balance > 0)
                                    <div class="text-danger">💵 رصيد الزبون {{ number_format($balance, 2) }}$ — يجب تصفيته قبل إصدار الفاتورة</div>
                                @else
                                    <div class="text-success">💵 لا رصيد عليه — جاهز للإصدار</div>
                                @endif
                                @if($openBonus)
                                    <div class="text-muted">
                                        📄 بونص #{{ $openBonus->id }} — {{ $statusLabels[$openBonus->status] ?? $openBonus->status }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

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
                                            غير مستحق بعد ({{ $progress->progress }}/{{ $progress->required }}).
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
                                    @if($eligible)
                                        <form method="POST" action="{{ route('admin.bonus.grant', $client->id) }}" class="mt-2 js-confirm"
                                              data-title="منح واعتماد فوري؟"
                                              data-text="البونص سيُعتمد مباشرة، ويُصدر الشهر المجاني تلقائياً إذا كان الرصيد صفراً."
                                              data-confirm="نعم، اعتمد فوراً">
                                            @csrf
                                            <input type="hidden" name="approve" value="1">
                                            <input type="hidden" name="notes" value="استحقاق 24 شهر — اعتماد فوري">
                                            <button class="btn btn-success w-100 btn-sm">منح + اعتماد فوري</button>
                                        </form>
                                    @endif
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
                                          data-text="سيصبح البونص معتمداً، ويُصدر الشهر المجاني تلقائياً إذا كان الرصيد صفراً."
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
                                          data-typed="حذف"
                                          data-danger="1">
                                        @csrf
                                        <input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="سبب الحذف (اختياري)" maxlength="255">
                                        <button class="btn btn-light-danger border border-danger w-100 btn-sm fw-bold">حذف نهائي</button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        @if($openBonus && $openBonus->status === 'approved')
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

                    {{-- ========== تعديل النقاط + الإعادة ========== --}}
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-bold mb-1" style="font-size:13.5px">✎ تعديل النقاط يدوياً</div>
                                <div class="text-muted mb-2" style="font-size:11.5px">رقم موجب يضيف، سالب يخصم (من −24 إلى 24).</div>
                                <form method="POST" action="{{ route('admin.bonus.points', $client->id) }}" class="row g-2 js-confirm"
                                      data-title="تعديل النقاط؟"
                                      data-text="سيُضاف التعديل إلى السجل باسمك."
                                      data-confirm="نعم، طبّق">
                                    @csrf
                                    <div class="col-4">
                                        <label class="form-label mb-1" style="font-size:11px">النقاط</label>
                                        <input type="number" name="points" class="form-control form-control-sm" value="1" min="-24" max="24" step="1" required>
                                    </div>
                                    <div class="col-8">
                                        <label class="form-label mb-1" style="font-size:11px">السبب</label>
                                        <input type="text" name="reason" class="form-control form-control-sm" placeholder="مثال: تعويض عن انقطاع" maxlength="255">
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-primary w-100 btn-sm">تطبيق التعديل</button>
                                    </div>
                                </form>

                                @if($adjustments->isNotEmpty())
                                    <div class="mt-2 pt-2" style="border-top:1px solid #eee;color:#4d5863">
                                        <div class="text-muted mb-1" style="font-size:11px">آخر التعديلات</div>
                                        @foreach($adjustments as $adj)
                                            <div class="d-flex justify-content-between" style="font-size:11.5px">
                                                <span>
                                                    @if($adj->type === 'reset')
                                                        <span class="text-danger">تصفير</span>
                                                    @else
                                                        <span class="{{ $adj->points > 0 ? 'text-success' : 'text-danger' }}">{{ $adj->points > 0 ? '+' : '' }}{{ $adj->points }}</span>
                                                    @endif
                                                    {{ $adj->reason ? '— '.$adj->reason : '' }}
                                                </span>
                                                <span class="text-muted">{{ substr((string) $adj->created_at, 0, 10) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-bold mb-1" style="font-size:13.5px">⟲ إعادة تعيين الدورة</div>
                                <div class="text-muted mb-2" style="font-size:11.5px">
                                    يُصفّر العدّاد إلى صفر ويُسجّل التعديلات اليدوية ويلغي أي بونص مفتوح. لا يمسّ الفواتير.
                                </div>
                                <form method="POST" action="{{ route('admin.bonus.reset', $client->id) }}" class="js-confirm"
                                      data-title="إعادة تعيين الدورة؟"
                                      data-text="هذا الإجراء لا يمكن التراجع عنه. اكتب «تصفير» للتأكيد."
                                      data-confirm="نعم، صفّر"
                                      data-typed="تصفير"
                                      data-danger="1">
                                    @csrf
                                    <input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="سبب الإعادة" maxlength="255">
                                    <button class="btn btn-danger w-100 btn-sm">تصفير الدورة</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h4 class="mb-3" style="font-size:15px">سجل البونص</h4>

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
                                var typed = form.dataset.typed || null;
                                var danger = form.dataset.danger === '1';

                                if (typeof Swal === 'undefined') {
                                    if (window.confirm(title + '\n' + text)) { form.dataset.confirmed = '1'; form.submit(); }
                                    return;
                                }

                                Swal.fire({
                                    title: title,
                                    html: text + (typed ? '<br><br><small>اكتب <b>' + typed + '</b> للمتابعة</small>' : ''),
                                    icon: danger ? 'warning' : 'question',
                                    input: typed ? 'text' : undefined,
                                    inputPlaceholder: typed ? typed : undefined,
                                    showCancelButton: true,
                                    confirmButtonText: okTxt,
                                    cancelButtonText: 'إلغاء',
                                    confirmButtonColor: danger ? '#f1416c' : '#3e97ff',
                                    preConfirm: function (value) {
                                        if (typed && (value || '').trim() !== typed) {
                                            Swal.showValidationMessage('النص غير مطابق');
                                            return false;
                                        }
                                        return true;
                                    }
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
