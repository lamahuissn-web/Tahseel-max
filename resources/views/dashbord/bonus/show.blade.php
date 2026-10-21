@extends('dashbord.layouts.master')

@section('css')
    <style>
        .bonus-card { border-top: 3px solid #17a2b8; }
        .month-box { border-radius: 10px; padding: 10px 8px; text-align: center; font-size: 12px; border: 1px solid #e4e6ef; }
        .month-on_time { background: #d4f5e2; border-color: #7ed6a5; }
        .month-catchup { background: #fff3cd; border-color: #ffd666; }
        .month-late    { background: #fde0e0; border-color: #f5a3a3; }
        .month-unpaid  { background: #f3f4f6; border-color: #dcdfe4; }
        .month-box .m { font-weight: 700; display: block; }
        .month-box .s { font-size: 11px; color: #555; }
    </style>
@endsection

@section('content')
    <div class="app-content flex-column-fluid" style="padding-top: 20px">
        <div class="container-fluid">

            <div class="mb-3">
                <a href="{{ route('admin.bonus.index') }}" class="btn btn-sm btn-light">&larr; رجوع للقائمة</a>
            </div>

            <div class="card shadow-sm bonus-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0">{{ $client->name }}</h3>
                        <span class="text-muted" style="font-size:13px">{{ $client->phone }}</span>
                    </div>
                    <div class="text-end">
                        <div style="font-size:26px;font-weight:700">{{ $progress->progress }} / {{ $progress->required }}</div>
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

                    <h4 class="mb-3" style="font-size:15px">سجل الأشهر</h4>
                    <div class="row g-2">
                        @foreach($progress->months as $m)
                            @php($cls = $m['state'] === 'on_time' ? 'month-on_time' : ($m['state'] === 'catchup' ? 'month-catchup' : ($m['state'] === 'late' ? 'month-late' : 'month-unpaid')))
                            <div class="col-4 col-md-2">
                                <div class="month-box {{ $cls }}">
                                    <span class="m">{{ $m['month'] }}</span>
                                    <span class="s">
                                        @if($m['credited']) ✓ محتسب
                                        @elseif($m['state'] === 'unpaid') — غير مدفوع
                                        @else ✗ غير محتسب
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                        @if(empty($progress->months))
                            <div class="col-12 text-muted">لا توجد فواتير اشتراك بعد تاريخ بدء الاحتساب.</div>
                        @endif
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
