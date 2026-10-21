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
                                        @case('catchup_used') 🟡 استدراك مستخدم @break
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
                                <div class="text-muted" style="font-size:12px">كسر متراكم</div>
                                <div class="fw-bold" style="font-size:13px">{{ $progress->carriedArrears }}</div>
                            </div>
                        </div>
                    </div>

                    <h4 class="mb-2" style="font-size:15px">سجل الأشهر</h4>
                    <div class="row g-2 mb-3">
                        @foreach($progress->months as $m)
                            @php($isCatchup = ($m['state'] ?? '') === 'catchup')
                            @php($cls = $isCatchup ? 'month-catchup' : (($m['state'] ?? '') === 'on_time' ? 'month-on_time' : (($m['state'] ?? '') === 'late' ? 'month-late' : 'month-unpaid')))
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

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if($eligible && ! $progress->hasOpenBonus)
                            <form method="POST" action="{{ route('admin.bonus.grant', $client->id) }}" class="flex-grow-1">
                                @csrf
                                <input type="hidden" name="approve" value="1">
                                <input type="hidden" name="notes" value="استحقاق 24 شهر">
                                <button class="btn btn-success w-100">🎁 منح الشهر المجاني (اعتماد فوري)</button>
                            </form>
                        @endif

                        @foreach($bonuses as $b)
                            @if($b->status === 'pending')
                                <form method="POST" action="{{ route('admin.bonus.approve', $b->id) }}" class="flex-grow-1">
                                    @csrf
                                    <button class="btn btn-primary w-100">✔ اعتماد البونص #{{ $b->id }}</button>
                                </form>
                            @elseif($b->status === 'approved')
                                <form method="POST" action="{{ route('admin.bonus.apply', $b->id) }}" class="flex-grow-1"
                                      onsubmit="return confirm('سيتم إنشاء فاتورة 0$ بتاريخ الاستحقاق القادم. متابعة؟')">
                                    @csrf
                                    <button class="btn btn-success w-100">🧾 إصدار فاتورة الشهر المجاني #{{ $b->id }}</button>
                                </form>
                            @endif
                        @endforeach
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-bold mb-2" style="font-size:14px">تعديل النقاط يدوياً</div>
                                <form method="POST" action="{{ route('admin.bonus.points', $client->id) }}" class="row g-2">
                                    @csrf
                                    <div class="col-4">
                                        <input type="number" name="points" class="form-control" value="1" min="-24" max="24" required>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="reason" class="form-control" placeholder="السبب">
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-outline-primary w-100">تطبيق التعديل</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-bold mb-2" style="font-size:14px">إعادة تعيين الدورة</div>
                                <form method="POST" action="{{ route('admin.bonus.reset', $client->id) }}" class="row g-2"
                                      onsubmit="return confirm('سيُصفَّر العدّاد ويُلغى أي بونص مفتوح. متابعة؟')">
                                    @csrf
                                    <div class="col-12">
                                        <input type="text" name="reason" class="form-control" placeholder="سبب الإعادة">
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-outline-danger w-100">إعادة تعيين إلى 0</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        @foreach($bonuses as $b)
                            @if(in_array($b->status, ['pending', 'approved'], true))
                                <div class="col-12">
                                    <form method="POST" action="{{ route('admin.bonus.cancel', $b->id) }}"
                                          onsubmit="return confirm('إلغاء البونص #{{ $b->id }}؟')">
                                        @csrf
                                        <button class="btn btn-sm btn-light-danger w-100">إلغاء البونص #{{ $b->id }}</button>
                                    </form>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <hr class="my-4">

                    <h4 class="mb-3" style="font-size:15px">سجل البونص</h4>
                    <div class="table-responsive"><table class="table table-sm" style="font-size:12px">
                        <thead><tr><th>#</th><th>تاريخ الاستحقاق</th><th>الحالة</th><th>المصدر</th><th>تاريخ الاستبدال</th><th>فاتورة</th></tr></thead>
                        <tbody>
                        @forelse($bonuses as $b)
                            <tr>
                                <td>{{ $b->id }}</td>
                                <td>{{ $b->earned_at }}</td>
                                <td>{{ $b->status }}</td>
                                <td>{{ $b->source }}</td>
                                <td>{{ $b->redeemed_at ?? '—' }}</td>
                                <td>{{ $b->redeemed_invoice_id ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">لا يوجد سجل بعد</td></tr>
                        @endforelse
                        </tbody>
                    </table></div>

                    <div class="alert alert-light mt-4 mb-0" style="font-size:13px">
                        <strong>ملاحظة:</strong> التقدّم محسوب من الفواتير والدفعات مباشرة ولا يُخزَّن، لذلك يبقى مطابقاً بعد أي استرجاع أو تعديل دفع.
                        الاعتماد وإصدار فاتورة الشهر المجاني سيُضاف في المرحلة القادمة.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
