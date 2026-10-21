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
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3"><div class="bonus-stat"><div class="num">{{ $summary['total'] }}</div><div class="lbl">زبائن الإنترنت</div></div></div>
                        <div class="col-6 col-md-3"><div class="bonus-stat"><div class="num text-success">{{ $summary['eligible'] }}</div><div class="lbl">مستحق الآن</div></div></div>
                        <div class="col-6 col-md-3"><div class="bonus-stat"><div class="num text-primary">{{ $summary['near'] }}</div><div class="lbl">قريب من الاستحقاق</div></div></div>
                        <div class="col-6 col-md-3"><div class="bonus-stat"><div class="num text-danger">{{ $summary['spiral'] }}</div><div class="lbl">دوامة</div></div></div>
                    </div>

                    <ul class="nav nav-tabs bonus-tabs mb-3">
                        <li class="nav-item"><a class="nav-link {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('admin.bonus.index') }}">الكل</a></li>
                        <li class="nav-item"><a class="nav-link {{ $filter === 'eligible' ? 'active' : '' }}" href="{{ route('admin.bonus.index', ['filter' => 'eligible']) }}">مستحق</a></li>
                        <li class="nav-item"><a class="nav-link {{ $filter === 'near' ? 'active' : '' }}" href="{{ route('admin.bonus.index', ['filter' => 'near']) }}">قريب</a></li>
                        <li class="nav-item"><a class="nav-link {{ $filter === 'spiral' ? 'active' : '' }}" href="{{ route('admin.bonus.index', ['filter' => 'spiral']) }}">دوامة</a></li>
                        <li class="nav-item"><a class="nav-link {{ $filter === 'excellent' ? 'active' : '' }}" href="{{ route('admin.bonus.index', ['filter' => 'excellent']) }}">💎 ممتاز</a></li>
                        <li class="nav-item"><a class="nav-link {{ $filter === 'catchup' ? 'active' : '' }}" href="{{ route('admin.bonus.index', ['filter' => 'catchup']) }}">🟡 عائد للانتظام</a></li>
                        <li class="nav-item"><a class="nav-link {{ $filter === 'hard_reset' ? 'active' : '' }}" href="{{ route('admin.bonus.index', ['filter' => 'hard_reset']) }}">متعثر</a></li>
                        <li class="nav-item"><a class="nav-link {{ $filter === 'open_bonus' ? 'active' : '' }}" href="{{ route('admin.bonus.index', ['filter' => 'open_bonus']) }}">بونص مفتوح</a></li>
                    </ul>

                    <form method="GET" action="{{ route('admin.bonus.index') }}" class="row g-2 mb-3">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <div class="col-8 col-md-5">
                            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="بحث بالاسم أو الهاتف...">
                        </div>
                        <div class="col-4 col-md-2">
                            <button class="btn btn-primary w-100">بحث</button>
                        </div>
                    </form>

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
