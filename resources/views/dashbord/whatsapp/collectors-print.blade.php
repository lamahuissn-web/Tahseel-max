<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>Collector Customers Print</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Tahoma, sans-serif; color: #111827; margin: 20px; }
        .toolbar { margin-bottom: 16px; display: flex; gap: 8px; justify-content: flex-start; }
        .btn { border: 1px solid #cbd5e1; background: #f8fafc; padding: 8px 12px; border-radius: 6px; cursor: pointer; text-decoration: none; color: #111827; }
        h1 { font-size: 22px; margin: 0 0 6px; }
        h2 { font-size: 18px; margin: 24px 0 8px; border-bottom: 2px solid #111827; padding-bottom: 6px; }
        .muted { color: #64748b; font-size: 12px; }
        .summary { display: flex; gap: 10px; flex-wrap: wrap; margin: 12px 0 20px; }
        .box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; min-width: 130px; }
        .box strong { display: block; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; page-break-inside: auto; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; font-size: 11px; vertical-align: top; }
        th { background: #f1f5f9; font-weight: bold; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        .collector { page-break-after: always; }
        .collector:last-child { page-break-after: auto; }
        .ltr { direction: ltr; text-align: left; }
        .conflict { color: #b45309; font-weight: bold; }
        @media print {
            .toolbar { display: none; }
            body { margin: 8mm; }
            .collector { page-break-after: always; }
            .collector:last-child { page-break-after: auto; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn" onclick="window.print()">Print / طباعة</button>
        <button class="btn" onclick="window.close()">Close</button>
    </div>

    <h1>Collector Customer Tracking List / قائمة متابعة المحصلين</h1>
    <div class="muted">
        Printed at: {{ $printedAt->format('Y-m-d H:i') }} · Source: all active customers matched by collector markers, not daily due reminder only.
    </div>

    <div class="summary">
        <div class="box"><span class="muted">Collectors</span><strong>{{ count($groups ?? []) }}</strong></div>
        <div class="box"><span class="muted">Customer rows</span><strong>{{ $summary['customers'] ?? 0 }}</strong></div>
        <div class="box"><span class="muted">Unique customers</span><strong>{{ $summary['unique_customers'] ?? 0 }}</strong></div>
        <div class="box"><span class="muted">Outstanding</span><strong>${{ number_format($summary['total_amount'] ?? 0, 2) }}</strong></div>
    </div>

    @forelse(($groups ?? []) as $group)
        <section class="collector">
            <h2>{{ $group['name'] ?: 'Unnamed Collector' }}</h2>
            <div class="muted">
                Phone: <span class="ltr">{{ $group['phone'] ?: '-' }}</span> ·
                Markers: <span class="ltr">{{ implode(', ', $group['markers'] ?? []) }}</span> ·
                Customers: {{ $group['customer_count'] ?? 0 }} ·
                Outstanding: ${{ number_format($group['total_amount'] ?? 0, 2) }}
                @if(($group['conflict_count'] ?? 0) > 0)
                    · <span class="conflict">Conflicts: {{ $group['conflict_count'] }}</span>
                @endif
            </div>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Due Amount</th>
                        <th>Due Date</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Marker</th>
                        <th>Visited</th>
                        <th>Collected</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($group['customers'] ?? []) as $customer)
                        <tr>
                            <td>{{ $customer['row_number'] ?? $loop->iteration }}</td>
                            <td>
                                {{ $customer['name'] }}
                                @if(!empty($customer['conflict']))
                                    <div class="conflict">Matches multiple collectors</div>
                                @endif
                            </td>
                            <td>${{ number_format($customer['total_amount'] ?? 0, 2) }}</td>
                            <td>{{ $customer['first_due_date_formatted'] ?? '-' }}</td>
                            <td class="ltr">{{ $customer['phone'] ?: '-' }}</td>
                            <td>{{ trim(($customer['address1'] ?? '') . ' ' . ($customer['address2'] ?? '')) ?: '-' }}</td>
                            <td class="ltr">{{ implode(', ', $customer['matched_markers'] ?? []) }}</td>
                            <td style="width: 60px;"></td>
                            <td style="width: 70px;"></td>
                            <td style="width: 150px;"></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="muted">No marked customers for this collector.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @empty
        <p class="muted">No collector rules found.</p>
    @endforelse
</body>
</html>
