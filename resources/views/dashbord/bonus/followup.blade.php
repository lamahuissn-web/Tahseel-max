@extends('dashbord.layouts.master')

@section('css')
    <style>
        .bn-stat { border-radius: 12px; padding: 12px 10px; background: #f8f9fa; text-align: center; border: 1px solid #eceff3; }
        .bn-stat .num { font-size: 22px; font-weight: 700; line-height: 1.15; }
        .bn-stat .lbl { font-size: 11px; color: #6c757d; margin-top: 2px; }
        .bn-pill { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .bn-ok   { background: #d4f5e2; color: #0f7b45; }
        .bn-warn { background: #fff3cd; color: #8a6d00; }
        .bn-bad  { background: #fde2e1; color: #a3140c; }
        .bn-mono { font-variant-numeric: tabular-nums; }
        .bn-tabs { flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .bn-tabs .nav-link { white-space: nowrap; font-weight: 600; font-size: 13px; color: #5e6278; }
        .bn-tabs .nav-link.active { color: #009ef7; border-bottom: 2px solid #009ef7; background: transparent; }
        .bn-habit { font-size: 12px; color: #5e6278; }
        .bn-habit .dots { letter-spacing: 2px; font-size: 11px; }
        .bn-dot-ok { color: #17c653; }
        .bn-dot-no { color: #f1416c; }
        .bn-table td { vertical-align: middle; }
        .bn-name a { font-weight: 700; color: #181c32; text-decoration: none; }
        .bn-name a:hover { color: #009ef7; }
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
                <h4 class="mb-1">🎁 البونص — متابعة الزبائن</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.bonus.settings') }}" class="btn btn-sm btn-light">
                        <i class="ki-duotone ki-gear fs-6"></i> الإعدادات
                    </a>
                </div>
            </div>
            <p class="text-muted small mb-3">
                <b>كل فاتورة تُدفع قبل استحقاقها + {{ $graceDays }} أيام = شهر محسوب.</b>
                الشهر لا يُمنح مقدماً — يُفتح عندما يمرّ موعده، والعدّ تراكمي بلا تصفير.
                العتبة الحالية: <b>{{ $required }}</b> شهراً · بداية العدّ: <b>{{ $startDate }}</b>.
                <span class="d-block mt-1">
                    «متأخر» تعني ديناً <b>تجاوز موعده</b> فقط — فاتورة الشهر القادم الصادرة مسبقاً لا تُحسب تأخيراً.
                </span>
            </p>

            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3 col-lg-2"><div class="bn-stat">
                    <div class="num">{{ number_format($counts['all']) }}</div>
                    <div class="lbl">إجمالي الزبائن</div>
                </div></div>
                <div class="col-6 col-md-3 col-lg-2"><div class="bn-stat">
                    <div class="num text-success">{{ $counts['eligible'] }}</div>
                    <div class="lbl">مستحق الآن</div>
                </div></div>
                <div class="col-6 col-md-3 col-lg-2"><div class="bn-stat">
                    <div class="num">${{ number_format($eligibleCost, 2) }}</div>
                    <div class="lbl">كلفة إصدار للجميع</div>
                </div></div>
                <div class="col-6 col-md-3 col-lg-2"><div class="bn-stat">
                    <div class="num text-warning">{{ $counts['verge'] }}</div>
                    <div class="lbl">على وشك الاستحقاق</div>
                </div></div>
                <div class="col-6 col-md-3 col-lg-2"><div class="bn-stat">
                    <div class="num text-info">{{ $counts['clean'] }}</div>
                    <div class="lbl">منتظم</div>
                </div></div>
                <div class="col-6 col-md-3 col-lg-2"><div class="bn-stat">
                    <div class="num text-danger">{{ $counts['late'] }}</div>
                    <div class="lbl">متأخر</div>
                </div></div>
            </div>

            <form method="GET" action="{{ route('admin.bonus.index') }}" class="d-flex flex-wrap gap-2 align-items-center mb-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="input-group" style="max-width: 320px">
                    <span class="input-group-text"><i class="ki-duotone ki-magnifier fs-5"></i></span>
                    <input type="text" name="q" value="{{ $search }}" class="form-control"
                           placeholder="بحث بالاسم أو الهاتف…">
                </div>
                <button class="btn btn-sm btn-primary" type="submit">بحث</button>
                @if($search !== '')
                    <a href="{{ route('admin.bonus.index', ['tab' => $tab]) }}" class="btn btn-sm btn-light">إلغاء البحث</a>
                @endif
                <span class="text-muted small ms-auto">
                    يُظهر {{ count($rows) }} من {{ number_format($total) }}
                </span>
            </form>

            <ul class="nav nav-tabs bn-tabs mb-3">
                @foreach($tabs as $key => $label)
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === $key ? 'active' : '' }}"
                           href="{{ route('admin.bonus.index', ['tab' => $key, 'q' => $search ?: null]) }}">
                            {{ $label }}
                            <span class="badge badge-light ms-1">{{ number_format($counts[$key] ?? 0) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="card">
                <div class="card-body p-0">
                    @if(count($rows) === 0)
                        <div class="p-5 text-center text-muted">
                            لا يوجد زبون مطابق — جرّب تبويباً آخر أو امسح البحث.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-row-dashed align-middle bn-table mb-0">
                                <thead>
                                    <tr class="text-muted fw-bold fs-7 text-uppercase">
                                        <th style="width:40px">#</th>
                                        <th>الزبون</th>
                                        <th class="text-center">التقدّم</th>
                                        <th class="text-center" style="min-width:150px">آخر شهر محتسب</th>
                                        <th class="text-center d-none d-md-table-cell" style="min-width:150px">الاستحقاق المتوقع</th>
                                        <th class="text-center">الرصيد</th>
                                        <th class="text-center" style="min-width:220px">طريقة الدفع</th>
                                        <th class="text-center">الحالة</th>
                                        <th class="text-center">الإجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $i => $row)
                                        <tr>
                                            <td class="bn-mono text-muted">{{ ($page - 1) * 50 + $i + 1 }}</td>
                                            <td class="bn-name">
                                                <a href="{{ route('admin.bonus.show', $row['clientId']) }}">{{ $row['name'] }}</a>
                                                <div class="small text-muted bn-mono">
                                                    {{ $row['phone'] }} · {{ $row['type'] }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="bn-pill {{ $row['eligible'] ? 'bn-ok' : ($row['status'] === 'clean' ? 'bn-ok' : 'bn-bad') }} bn-mono">
                                                    {{ $row['credits'] }}/{{ $row['required'] }}
                                                </span>
                                                @if($row['remaining'] > 0)
                                                    <div class="small text-muted">باقي {{ $row['remaining'] }}</div>
                                                @endif
                                            </td>
                                            <td class="text-center bn-mono">
                                                {{ $row['lastCreditedMonth'] ?? '—' }}
                                                @if($row['streak'] > 1)
                                                    <div class="small text-muted">{{ $row['streak'] }} أشهر متتالية</div>
                                                @endif
                                            </td>
                                            <td class="text-center d-none d-md-table-cell bn-mono">
                                                {{ $row['nextEligibleMonth'] ?? '—' }}
                                            </td>
                                            <td class="text-center bn-mono {{ $row['overdue'] > 0 ? 'text-danger fw-bold' : ($row['balance'] > 0 ? 'text-warning' : 'text-success') }}">
                                                {{ number_format($row['balance'], 2) }}$
                                                @if($row['overdue'] > 0)
                                                    <div class="small text-danger">مستحق {{ number_format($row['overdue'], 2) }}$</div>
                                                @elseif($row['balance'] > 0)
                                                    <div class="small text-muted">غير مستحق بعد</div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="bn-habit">{{ $row['habitLabel'] }}</div>
                                                <div class="bn-habit dots">
                                                    @foreach($row['months'] as $m)
                                                        <span class="{{ $m['credited'] ? 'bn-dot-ok' : 'bn-dot-no' }}"
                                                              title="{{ $m['month'] }} · {{ $m['credited'] ? 'محتسب' : 'غير محتسب' }}">●</span>
                                                    @endforeach
                                                    @if($row['missed'] > 0)
                                                        <span class="text-muted">· ناقص {{ $row['missed'] }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($row['eligible'])
                                                    <span class="bn-pill bn-ok">مستحق</span>
                                                @elseif($row['verge'])
                                                    <span class="bn-pill bn-warn">على وشك</span>
                                                @elseif($row['status'] === 'clean')
                                                    <span class="bn-pill bn-ok">منتظم</span>
                                                @else
                                                    <span class="bn-pill bn-bad">متأخر</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.bonus.show', $row['clientId']) }}"
                                                   class="btn btn-sm btn-light-primary py-1 px-2">تفاصيل</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            @if($lastPage > 1)
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">صفحة {{ $page }} من {{ $lastPage }}</div>
                    <div class="d-flex gap-2">
                        @if($page > 1)
                            <a class="btn btn-sm btn-light"
                               href="{{ route('admin.bonus.index', ['tab' => $tab, 'q' => $search ?: null, 'page' => $page - 1]) }}">السابق</a>
                        @endif
                        @if($page < $lastPage)
                            <a class="btn btn-sm btn-light"
                               href="{{ route('admin.bonus.index', ['tab' => $tab, 'q' => $search ?: null, 'page' => $page + 1]) }}">التالي</a>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection
