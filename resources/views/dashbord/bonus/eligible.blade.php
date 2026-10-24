@extends('dashbord.layouts.master')

@section('css')
    <style>
        .bn-stat { border-radius: 12px; padding: 12px 10px; background: #f8f9fa; text-align: center; border: 1px solid #eceff3; }
        .bn-stat .num { font-size: 22px; font-weight: 700; line-height: 1.15; }
        .bn-stat .lbl { font-size: 11px; color: #6c757d; margin-top: 2px; }
        .bn-pill { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .bn-ok   { background: #d4f5e2; color: #0f7b45; }
        .bn-warn { background: #fff3cd; color: #8a6d00; }
        .bn-mono { font-variant-numeric: tabular-nums; }
        .bn-tabs { flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .bn-tabs .nav-link { white-space: nowrap; font-weight: 600; font-size: 13px; }
    </style>
@endsection

@section('content')
    <div class="app-content flex-column-fluid" style="padding-top: 14px">
        <div class="container-fluid px-2 px-md-4">

            @if(! $enabled)
                <div class="alert alert-warning py-2">
                    البرنامج متوقف — الأرقام للاطلاع فقط. شغّله من
                    <a href="{{ route('admin.bonus.settings') }}">الإعدادات</a>.
                </div>
            @endif

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                <h4 class="mb-1">🎁 البونص — قائمة المستحقين</h4>
                <a href="{{ route('admin.bonus.settings') }}" class="btn btn-sm btn-light">الإعدادات</a>
            </div>
            <p class="text-muted small mb-3">
                شهر يُحتسب إذا كان رصيد الزبون صفراً عند استحقاق الفاتورة التالية (مع {{ $graceDays }} أيام سماح).
                العتبة الحالية: <b>{{ $required }}</b> شهراً · بداية العدّ: <b>{{ $startDate }}</b>.
            </p>

            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3"><div class="bn-stat">
                    <div class="num text-success">{{ count($eligible) }}</div>
                    <div class="lbl">مستحق الآن</div>
                </div></div>
                <div class="col-6 col-md-3"><div class="bn-stat">
                    <div class="num">${{ number_format($eligibleCost, 2) }}</div>
                    <div class="lbl">كلفة إصدار للجميع</div>
                </div></div>
                <div class="col-6 col-md-3"><div class="bn-stat">
                    <div class="num text-warning">{{ count($verge) }}</div>
                    <div class="lbl">على وشك الاستحقاق</div>
                </div></div>
                <div class="col-6 col-md-3"><div class="bn-stat">
                    <div class="num">${{ number_format($vergeCost, 2) }}</div>
                    <div class="lbl">الكلفة المتوقعة قريباً</div>
                </div></div>
            </div>

            <ul class="nav nav-tabs bn-tabs mb-3" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-eligible">مستحق للصرف</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-verge">على وشك الاستحقاق</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-eligible">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            @if(count($eligible) === 0)
                                <div class="p-4 text-center text-muted">
                                    لا يوجد مستحق الآن — لم يكمل أحد العتبة ({{ $required }} شهراً) وهو بلا دين.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th style="width:44px">#</th>
                                            <th>الزبون</th>
                                            <th class="text-center">التقدّم</th>
                                            <th class="text-center d-none d-md-table-cell">آخر شهر محتسب</th>
                                            <th class="text-center">الرصيد</th>
                                            <th class="text-center">القيمة</th>
                                            <th class="text-center">الإجراء</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($eligible as $i => $row)
                                            <tr>
                                                <td class="bn-mono">{{ $i + 1 }}</td>
                                                <td>
                                                    <a href="{{ route('admin.bonus.show', $row['clientId']) }}">{{ $row['name'] }}</a>
                                                    @if($row['phone'])
                                                        <div class="small text-muted bn-mono">{{ $row['phone'] }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="bn-pill bn-ok bn-mono">{{ $row['credits'] }}/{{ $row['required'] }}</span>
                                                </td>
                                                <td class="text-center d-none d-md-table-cell bn-mono">{{ $row['lastCreditedMonth'] ?? '—' }}</td>
                                                <td class="text-center bn-mono">{{ number_format($row['balance'], 2) }}$</td>
                                                <td class="text-center bn-mono">{{ number_format($row['price'], 2) }}$</td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.bonus.show', $row['clientId']) }}" class="btn btn-sm btn-primary">
                                                        إصدار
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                        <tr>
                                            <th colspan="4" class="text-end">الإجمالي</th>
                                            <th class="text-center bn-mono">0.00$</th>
                                            <th class="text-center bn-mono">{{ number_format($eligibleCost, 2) }}$</th>
                                            <th></th>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <p class="small text-muted mt-2 mb-0">
                        الزر «إصدار» ينقلك إلى صفحة الزبون حيث تُصدر الشهر المجاني — لا إصدار تلقائي.
                    </p>
                </div>

                <div class="tab-pane fade" id="tab-verge">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            @if(count($verge) === 0)
                                <div class="p-4 text-center text-muted">لا أحد على وشك الاستحقاق.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>الزبون</th>
                                            <th class="text-center">التقدّم</th>
                                            <th class="text-center">باقي</th>
                                            <th class="text-center">الرصيد</th>
                                            <th class="text-center">الشهر المتوقع</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($verge as $row)
                                            <tr>
                                                <td><a href="{{ route('admin.bonus.show', $row['clientId']) }}">{{ $row['name'] }}</a></td>
                                                <td class="text-center bn-mono">{{ $row['credits'] }}/{{ $required }}</td>
                                                <td class="text-center"><span class="bn-pill bn-warn bn-mono">{{ $row['remaining'] }}</span></td>
                                                <td class="text-center bn-mono">{{ number_format($row['balance'], 2) }}$</td>
                                                <td class="text-center bn-mono">{{ $row['nextEligibleMonth'] }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
