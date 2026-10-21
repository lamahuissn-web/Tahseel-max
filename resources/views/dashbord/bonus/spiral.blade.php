@extends('dashbord.layouts.master')

@section('css')
    <style>
        .spiral-card { border: 1px solid #f1dede; border-radius: 14px; padding: 12px 14px; margin-bottom: 10px; background: #fff; }
        .spiral-card .name { font-weight: 700; font-size: 15px; }
        .spiral-card .kv { font-size: 12px; color: #6c757d; }
        .spiral-card .kv strong { color: #212529; }
        .spiral-card .amount { font-weight: 700; color: #a31919; direction: ltr; }
    </style>
@endsection

@section('content')
    <div class="app-content flex-column-fluid" style="padding-top: 14px">
        <div class="container-fluid px-2 px-md-4">
            <div class="mb-3">
                <a href="{{ route('admin.bonus.index') }}" class="btn btn-sm btn-light">&larr; رجوع</a>
            </div>

            <div class="card shadow-sm" style="border-top: 3px solid #dc3545">
                <div class="card-header py-3">
                    <h3 class="card-title mb-0" style="font-size:17px">زبائن الدوامة ({{ count($rows) }})</h3>
                    <div class="text-muted" style="font-size:12px">
                        يدفعون الفواتير الحديثة ويتركون فواتير أقدم مفتوحة — لا يُحتسب لهم أي شهر حتى يسدّدوا.
                    </div>
                </div>
                <div class="card-body pt-3 px-2 px-md-4">

                    {{-- ============ phone: cards ============ --}}
                    <div class="d-md-none">
                        @forelse($rows as $r)
                            <div class="spiral-card">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="name">{{ $r['name'] }}</div>
                                    <span class="amount">{{ number_format($r['owed'], 2) }} $</span>
                                </div>
                                <div class="kv mb-1">
                                    <strong>{{ $r['unpaid_count'] }}</strong> فاتورة مفتوحة ·
                                    أقدمها <strong>{{ $r['oldest_due'] }}</strong> ({{ $r['days_old'] }} يوم)
                                </div>
                                <div class="kv mb-2">
                                    آخر دفعة:
                                    <strong>{{ $r['last_payment'] ? \Illuminate\Support\Carbon::parse($r['last_payment'])->format('Y-m-d') : '—' }}</strong>
                                </div>
                                <a href="{{ route('admin.bonus.show', $r['client_id']) }}" class="btn btn-sm btn-light-primary w-100">التفاصيل</a>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">لا يوجد زبائن في هذه الحالة</div>
                        @endforelse
                    </div>

                    {{-- ============ desktop: table ============ --}}
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover" style="font-size:13px">
                            <thead>
                                <tr>
                                    <th>الزبون</th>
                                    <th>الفواتير المفتوحة</th>
                                    <th>أقدم شهر غير مدفوع</th>
                                    <th>عمر الدين (يوم)</th>
                                    <th>المبلغ المستحق</th>
                                    <th>آخر دفعة</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($rows as $r)
                                <tr>
                                    <td class="fw-bold">{{ $r['name'] }}</td>
                                    <td><span class="badge bg-light-danger text-danger">{{ $r['unpaid_count'] }}</span></td>
                                    <td>{{ $r['oldest_due'] }}</td>
                                    <td>{{ $r['days_old'] }}</td>
                                    <td>{{ number_format($r['owed'], 2) }} $</td>
                                    <td>{{ $r['last_payment'] ? \Illuminate\Support\Carbon::parse($r['last_payment'])->format('Y-m-d') : '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.bonus.show', $r['client_id']) }}" class="btn btn-sm btn-light-primary">التفاصيل</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">لا يوجد زبائن في هذه الحالة</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
