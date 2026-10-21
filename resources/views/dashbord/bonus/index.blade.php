@extends('dashbord.layouts.master')

@section('css')
    <style>
        .bonus-card { border-top: 3px solid #17a2b8; }
        .bonus-stat { border-radius: 12px; padding: 12px 8px; background: #f8f9fa; text-align: center; }
        .bonus-stat .num { font-size: 22px; font-weight: 700; line-height: 1.15; }
        .bonus-stat .lbl { font-size: 11px; color: #6c757d; margin-top: 2px; }
        .progress { height: 8px; border-radius: 4px; background: #eceff3; }
        .badge-soft-success { background: #d4f5e2; color: #0f7b45; }
        .badge-soft-warning { background: #fff3cd; color: #8a6d00; }
        .badge-soft-danger  { background: #fde0e0; color: #a31919; }
        .badge-soft-info    { background: #dceeff; color: #0b5b8a; }
        .badge-soft-dark    { background: #e6e6e6; color: #333; }

        /* tabs scroll horizontally on a phone instead of wrapping into rows */
        .bonus-tabs { flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 4px; }
        .bonus-tabs::-webkit-scrollbar { height: 0; }
        .bonus-tabs .nav-link { white-space: nowrap; font-weight: 600; font-size: 13px; padding: .5rem .7rem; }

        /* clickable status cards */
        .status-card { display: block; text-decoration: none; color: inherit; border: 1px solid #e9ecef; position: relative; }
        .status-card:hover { text-decoration: none; color: inherit; border-color: #cfd6dd; }
        .status-card .pct { font-size: 10px; color: #9aa1a9; margin-top: 1px; }
        .status-card.active { border: 2px solid #4b7bec; background: #eef3ff; }
        .status-card.sc-excellent { border-left: 4px solid #20c997; }
        .status-card.sc-compliant { border-left: 4px solid #28a745; }
        .status-card.sc-recovering { border-left: 4px solid #ffc107; }
        .status-card.sc-late { border-left: 4px solid #17a2b8; }
        .status-card.sc-spiral { border-left: 4px solid #dc3545; }
        .status-card.sc-hard { border-left: 4px solid #343a40; }

        /* small chips */
        .bonus-chip {
            display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px; border-radius: 18px;
            background: #f1f3f5; color: #495057; font-size: 12px; font-weight: 600; text-decoration: none;
            white-space: nowrap;
        }
        .bonus-chip:hover { background: #e6e9ec; color: #212529; text-decoration: none; }
        .bonus-chip.active { background: #4b7bec; color: #fff; }
        .bonus-chip.muted { background: #f8f9fa; color: #8a9099; font-weight: 500; }
        .bonus-headline { font-size: 12.5px; color: #6c757d; }

        /* phone customer card */
        .bonus-client-card {
            border: 1px solid #e9ecef; border-radius: 14px; padding: 12px 14px; margin-bottom: 10px;
            background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.05);
        }
        .bonus-client-card .name { font-weight: 700; font-size: 15px; }
        .bonus-client-card .phone { font-size: 12px; color: #8a9099; direction: ltr; text-align: right; }
        .bonus-client-card .meta { font-size: 12px; color: #6c757d; }
        .bonus-client-card .state-badge { font-size: 12px; padding: 4px 9px; border-radius: 20px; white-space: nowrap; }

        @media (max-width: 767.98px) {
            .bonus-page-title { font-size: 17px; }
        }
    </style>
@endsection

@section('content')
    <div class="app-content flex-column-fluid" style="padding-top: 14px">
        <div class="container-fluid px-2 px-md-4">

            <div class="card shadow-sm bonus-card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                    <h3 class="card-title mb-0 bonus-page-title">البونص والمكافآت</h3>
                    <div class="d-flex align-items-center gap-2">
                        @if(!$enabled)
                            <span class="badge badge-soft-dark">متوقف</span>
                        @else
                            <span class="badge badge-soft-success">مفعّل</span>
                        @endif
                        <a href="{{ route('admin.bonus.spiral') }}" class="btn btn-sm btn-light-danger">الدوامة</a>
                        <a href="{{ route('admin.bonus.log') }}" class="btn btn-sm btn-light-secondary">السجل</a>
                        <a href="{{ route('admin.bonus.settings') }}" class="btn btn-sm btn-light-primary">الإعدادات</a>
                    </div>
                </div>

                <div class="card-body pt-3 px-2 px-md-4">
                    @php
                        $pct = fn ($n) => $summary['total'] > 0 ? round($n / $summary['total'] * 100) : 0;
                        $statusCards = [
                            ['key' => 'excellent',  'label' => 'ممتاز',           'emoji' => '💎', 'cls' => 'sc-excellent'],
                            ['key' => 'compliant',  'label' => 'منتظم',           'emoji' => '🟢', 'cls' => 'sc-compliant'],
                            ['key' => 'recovering', 'label' => 'عائد للانتظام',   'emoji' => '🟡', 'cls' => 'sc-recovering'],
                            ['key' => 'late',       'label' => 'متأخر',           'emoji' => '🟠', 'cls' => 'sc-late'],
                            ['key' => 'spiral',     'label' => 'دوامة',           'emoji' => '🔴', 'cls' => 'sc-spiral'],
                            ['key' => 'hard_reset', 'label' => 'متعثر',           'emoji' => '⚫', 'cls' => 'sc-hard'],
                        ];
                    @endphp

                    {{-- ===== الحالات: كل بطاقة تُرشِّح القائمة ===== --}}
                    <div class="row g-2 mb-2">
                        @foreach($statusCards as $card)
                            <div class="col-4 col-md-2">
                                <a href="{{ route('admin.bonus.index', ['filter' => $card['key'], 'search' => $search, 'sort' => $sort]) }}"
                                   class="bonus-stat status-card {{ $card['cls'] }} {{ $filter === $card['key'] ? 'active' : '' }}">
                                    <div class="num">{{ $summary[$card['key']] }}</div>
                                    <div class="lbl">{{ $card['emoji'] }} {{ $card['label'] }}</div>
                                    <div class="pct">{{ $pct($summary[$card['key']]) }}%</div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <div class="bonus-headline mb-3">
                        بلا ديون: <strong class="text-success">{{ $summary['no_debt'] }}</strong>
                        ({{ $pct($summary['no_debt']) }}%) ·
                        عليهم ديون: <strong class="text-danger">{{ $summary['with_debt'] }}</strong>
                        ({{ $pct($summary['with_debt']) }}%) ·
                        متوسط التقدّم: <strong>{{ $summary['avg_progress'] }} / {{ $settings['bonus_months_required'] ?? 24 }}</strong>
                    </div>

                    {{-- ===== شرائح سريعة ===== --}}
                    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                        <a class="bonus-chip {{ $filter === 'all' ? 'active' : '' }}"
                           href="{{ route('admin.bonus.index', ['search' => $search, 'sort' => $sort]) }}">الكل ({{ $summary['total'] }})</a>
                        <a class="bonus-chip {{ $filter === 'eligible' ? 'active' : '' }}"
                           href="{{ route('admin.bonus.index', ['filter' => 'eligible', 'search' => $search, 'sort' => $sort]) }}">🏆 مستحق ({{ $summary['eligible'] }})</a>
                        <a class="bonus-chip {{ $filter === 'near' ? 'active' : '' }}"
                           href="{{ route('admin.bonus.index', ['filter' => 'near', 'search' => $search, 'sort' => $sort]) }}">⏳ قريب ({{ $summary['near'] }})</a>
                        <a class="bonus-chip {{ $filter === 'open_bonus' ? 'active' : '' }}"
                           href="{{ route('admin.bonus.index', ['filter' => 'open_bonus', 'search' => $search, 'sort' => $sort]) }}">🎁 بونص مفتوح ({{ $summary['open'] }})</a>
                        <a class="bonus-chip {{ $filter === 'debt' ? 'active' : '' }}"
                           href="{{ route('admin.bonus.index', ['filter' => 'debt', 'search' => $search, 'sort' => $sort]) }}">⚠️ عليهم ديون ({{ $summary['with_debt'] }})</a>
                        <span class="bonus-chip muted">⛔ خارج البرنامج: {{ $summary['excluded'] }}</span>
                    </div>

                    {{-- ===== بحث وترتيب وتصدير ===== --}}
                    <form method="GET" action="{{ route('admin.bonus.index') }}" class="row g-2 mb-2">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <div class="col-12 col-md-4">
                            <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm"
                                   placeholder="بحث بالاسم أو الهاتف...">
                        </div>
                        <div class="col-6 col-md-3">
                            <select name="sort" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="progress_desc" {{ $sort === 'progress_desc' ? 'selected' : '' }}>النقاط: الأعلى أولاً</option>
                                <option value="progress_asc"  {{ $sort === 'progress_asc' ? 'selected' : '' }}>النقاط: الأدنى أولاً</option>
                                <option value="clean"         {{ $sort === 'clean' ? 'selected' : '' }}>الأشهر النظيفة: الأكثر</option>
                                <option value="name"          {{ $sort === 'name' ? 'selected' : '' }}>الاسم (أ–ي)</option>
                            </select>
                        </div>
                        <div class="col-3 col-md-2">
                            <button class="btn btn-primary btn-sm w-100">بحث</button>
                        </div>
                        <div class="col-3 col-md-2">
                            <a href="{{ route('admin.bonus.index', ['filter' => $filter, 'search' => $search, 'sort' => $sort, 'export' => 'csv']) }}"
                               class="btn btn-light-success btn-sm w-100">⬇ CSV</a>
                        </div>
                    </form>

                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="bonus-headline">
                            عرض <strong>{{ $paginator->total() }}</strong> زبون
                            @if($search !== '')
                                · بحث: «{{ $search }}»
                            @endif
                        </div>
                        @if($filter !== 'all' || $search !== '' || $sort !== 'progress_desc')
                            <a href="{{ route('admin.bonus.index') }}" class="bonus-chip">↺ إعادة تعيين</a>
                        @endif
                    </div>

                    {{-- ============ phone: cards ============ --}}
                    <div class="d-md-none">
                        @forelse($paginator as $row)
                            @php($p = $row['progress'])
                                                        <div class="bonus-client-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="name">{{ $row['client']->name }}</div>
                                        <div class="phone">{{ $row['client']->phone }}</div>
                                    </div>
                                    @switch($p->status)
                                        @case('excellent') <span class="state-badge badge-soft-success">💎 ممتاز</span> @break
                                        @case('compliant') <span class="state-badge badge-soft-success">🟢 منتظم</span> @break
                                        @case('recovering') <span class="state-badge badge-soft-warning">🟡 عائد للانتظام</span> @break
                                        
                                        @case('late') <span class="state-badge badge-soft-info">🟠 متأخر</span> @break
                                        @case('spiral') <span class="state-badge badge-soft-danger">🔴 دوامة</span> @break
                                        @case('hard_reset') <span class="state-badge badge-soft-dark">⚫ متعثر</span> @break
                                        @default <span class="state-badge badge-soft-dark">{{ $p->status }}</span>
                                    @endswitch
                                </div>

                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="progress flex-grow-1">
                                        <div class="progress-bar bg-success" style="width: {{ min(100, ($p->progress / max(1, $p->required)) * 100) }}%"></div>
                                    </div>
                                    <span class="fw-bold" style="font-size:13px">{{ $p->progress }}/{{ $p->required }}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-end">
                                    <div class="meta">
                                        آخر شهر: <strong>{{ $p->lastCreditedMonth ?? '—' }}</strong><br>
                                        المتوقع: <strong>{{ $p->nextEligibleMonth ?? '—' }}</strong>
                                    </div>
                                    <a href="{{ route('admin.bonus.show', $row['client']->id) }}" class="btn btn-sm btn-light-primary">التفاصيل</a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">لا يوجد زبائن مطابقون</div>
                        @endforelse
                    </div>

                    {{-- ============ desktop: table ============ --}}
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover" style="font-size:13px">
                            <thead>
                                <tr>
                                    <th>الزبون</th>
                                    <th style="width:190px">التقدّم</th>
                                    <th>آخر شهر محتسب</th>
                                    <th>الشهر المتوقع</th>
                                    <th>الحالة</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($paginator as $row)
                                @php($p = $row['progress'])
                                                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $row['client']->name }}</div>
                                        <div class="text-muted" style="font-size:12px">{{ $row['client']->phone }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1">
                                                <div class="progress-bar bg-success" style="width: {{ min(100, ($p->progress / max(1, $p->required)) * 100) }}%"></div>
                                            </div>
                                            <span class="fw-bold">{{ $p->progress }}/{{ $p->required }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $p->lastCreditedMonth ?? '—' }}</td>
                                    <td>{{ $p->nextEligibleMonth ?? '—' }}</td>
                                    <td>
                                        @switch($p->status)
                                            @case('excellent') <span class="badge badge-soft-success">💎 ممتاز</span> @break
                                            @case('compliant') <span class="badge badge-soft-success">🟢 منتظم</span> @break
                                            @case('recovering') <span class="badge badge-soft-warning">🟡 عائد للانتظام</span> @break
                                            
                                            @case('late') <span class="badge badge-soft-info">🟠 متأخر</span> @break
                                            @case('spiral') <span class="badge badge-soft-danger">🔴 دوامة</span> @break
                                            @case('hard_reset') <span class="badge badge-soft-dark">⚫ متعثر</span> @break
                                            @default <span class="badge badge-soft-dark">{{ $p->status }}</span>
                                        @endswitch
                                        @if($p->hasOpenBonus)
                                            <span class="badge badge-soft-warning">بونص مفتوح</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.bonus.show', $row['client']->id) }}" class="btn btn-sm btn-light-primary">التفاصيل</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">لا يوجد زبائن مطابقون</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $paginator->withQueryString()->links() }}</div>
                </div>
            </div>

        </div>
    </div>
@endsection
