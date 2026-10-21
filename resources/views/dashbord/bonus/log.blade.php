@extends('dashbord.layouts.master')

@section('css')
    <style>
        .log-card { border: 1px solid #eef0f3; border-radius: 12px; padding: 10px 12px; margin-bottom: 8px; background: #fff; }
        .log-card .who { font-weight: 700; font-size: 14px; }
        .log-card .meta { font-size: 12px; color: #6c757d; }
        .st-pending { background: #fff3cd; color: #8a6d00; }
        .st-approved { background: #dceeff; color: #0b5b8a; }
        .st-redeemed { background: #d4f5e2; color: #0f7b45; }
        .st-cancelled { background: #e6e6e6; color: #333; }
    </style>
@endsection

@section('content')
    <div class="app-content flex-column-fluid" style="padding-top: 14px">
        <div class="container-fluid px-2 px-md-4">
            <div class="mb-3 d-flex gap-2">
                <a href="{{ route('admin.bonus.index') }}" class="btn btn-sm btn-light">&larr; رجوع</a>
            </div>

            <div class="card shadow-sm mb-3" style="border-top: 3px solid #0d6efd">
                <div class="card-header py-3">
                    <h3 class="card-title mb-0" style="font-size:17px">سجل البونص</h3>
                </div>
                <div class="card-body px-2 px-md-4">

                    {{-- phone: cards --}}
                    <div class="d-md-none">
                        @forelse($bonuses as $b)
                            <div class="log-card">
                                <div class="d-flex justify-content-between">
                                    <div class="who">{{ $b->client_name ?? 'زبون #'.$b->client_id }}</div>
                                    <span class="badge st-{{ $b->status }}">{{ $b->status }}</span>
                                </div>
                                <div class="meta">استحقاق: {{ $b->earned_at }} · المصدر: {{ $b->source }}</div>
                                @if($b->redeemed_at)
                                    <div class="meta">استُبدل: {{ $b->redeemed_at }} · فاتورة #{{ $b->redeemed_invoice_id }}</div>
                                @endif
                                @if($b->notes)
                                    <div class="meta">{{ $b->notes }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">لا يوجد سجل بعد</div>
                        @endforelse
                    </div>

                    {{-- desktop: table --}}
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-sm" style="font-size:13px">
                            <thead><tr><th>#</th><th>الزبون</th><th>الحالة</th><th>المصدر</th><th>تاريخ الاستحقاق</th><th>الاستبدال</th><th>الفاتورة</th><th>ملاحظات</th></tr></thead>
                            <tbody>
                            @forelse($bonuses as $b)
                                <tr>
                                    <td>{{ $b->id }}</td>
                                    <td>{{ $b->client_name ?? '#'.$b->client_id }}</td>
                                    <td><span class="badge st-{{ $b->status }}">{{ $b->status }}</span></td>
                                    <td>{{ $b->source }}</td>
                                    <td>{{ $b->earned_at }}</td>
                                    <td>{{ $b->redeemed_at ?? '—' }}</td>
                                    <td>{{ $b->redeemed_invoice_id ?? '—' }}</td>
                                    <td class="text-muted">{{ $b->notes ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-3">لا يوجد سجل بعد</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm" style="border-top: 3px solid #6c757d">
                <div class="card-header py-3">
                    <h3 class="card-title mb-0" style="font-size:17px">تعديلات النقاط وإعادة التعيين</h3>
                </div>
                <div class="card-body px-2 px-md-4">
                    <div class="table-responsive">
                        <table class="table table-sm" style="font-size:12px">
                            <thead><tr><th>#</th><th>الزبون</th><th>النوع</th><th>النقاط</th><th>السبب</th><th>التاريخ</th></tr></thead>
                            <tbody>
                            @forelse($adjustments as $a)
                                <tr>
                                    <td>{{ $a->id }}</td>
                                    <td>{{ $a->client_name ?? '#'.$a->client_id }}</td>
                                    <td>{{ $a->type === 'reset' ? 'إعادة تعيين' : 'نقاط' }}</td>
                                    <td>{{ $a->type === 'reset' ? '—' : (($a->points > 0 ? '+' : '').$a->points) }}</td>
                                    <td class="text-muted">{{ $a->reason ?? '—' }}</td>
                                    <td>{{ $a->created_at }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">لا يوجد سجل بعد</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
