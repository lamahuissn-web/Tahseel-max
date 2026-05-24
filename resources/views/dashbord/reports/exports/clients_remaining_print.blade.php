<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ trans('reports.clients_remaining_report') }}</title>
    <style>
        body {
            font-family: 'Cairo', 'Tahoma', sans-serif;
            margin: 20px;
            color: #1f2937;
        }
        h2 {
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: center;
        }
        th {
            background-color: #f3f4f6;
        }
        .summary {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }
        .summary-item {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px 15px;
            min-width: 140px;
            text-align: center;
        }
        .text-muted {
            color: #6b7280;
        }
        .text-success { color: #15803d; }
        .text-danger { color: #b91c1c; }
        .text-primary { color: #1d4ed8; }
    </style>
</head>
<body>
    <h2>{{ trans('reports.clients_remaining_report') }}</h2>
    <div class="text-muted">{{ now()->format('Y-m-d H:i') }}</div>

    <div class="summary">
        <div class="summary-item">
            <div class="text-muted">{{ trans('reports.total_clients') }}</div>
            <strong>{{ number_format($totals->clients_count ?? 0) }}</strong>
        </div>
        <div class="summary-item">
            <div class="text-muted">{{ trans('reports.total_invoices') }}</div>
            <strong>{{ number_format($totals->invoices_count ?? 0) }}</strong>
        </div>
        <div class="summary-item">
            <div class="text-muted">{{ trans('reports.total_amount') }}</div>
            <strong class="text-primary">{{ number_format($totals->total_amount ?? 0, 2) }} {{ $currency }}</strong>
        </div>
        <div class="summary-item">
            <div class="text-muted">{{ trans('reports.total_paid') }}</div>
            <strong class="text-success">{{ number_format($totals->total_paid ?? 0, 2) }} {{ $currency }}</strong>
        </div>
        <div class="summary-item">
            <div class="text-muted">{{ trans('reports.total_remaining') }}</div>
            <strong class="text-danger">{{ number_format($totals->total_remaining ?? 0, 2) }} {{ $currency }}</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ trans('clients.name') }}</th>
                <th>{{ trans('clients.phone') }}</th>
                <th>{{ trans('clients.client_type') }}</th>
                <th>{{ trans('clients.subscription') }}</th>
                <th>{{ trans('reports.total_invoices') }}</th>
                <th>{{ trans('reports.total_amount') }}</th>
                <th>{{ trans('reports.total_paid') }}</th>
                <th>{{ trans('reports.total_remaining') }}</th>
                <th>{{ trans('reports.last_due_date') }}</th>
                <th>{{ trans('clients.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->phone ?? '-' }}</td>
                    <td>
                        @php $typeKey = 'clients.' . ($row->client_type ?? ''); @endphp
                        {{ trans()->has($typeKey) ? trans($typeKey) : ($row->client_type ?? '-') }}
                    </td>
                    <td>{{ $row->subscription_name ?? '-' }}</td>
                    <td>{{ $row->invoices_count ?? 0 }}</td>
                    <td>{{ number_format($row->total_amount ?? 0, 2) }} {{ $currency }}</td>
                    <td>{{ number_format($row->total_paid ?? 0, 2) }} {{ $currency }}</td>
                    <td>{{ number_format($row->total_remaining ?? 0, 2) }} {{ $currency }}</td>
                    <td>{{ $row->latest_due_date ? \Carbon\Carbon::parse($row->latest_due_date)->format('Y-m-d') : '-' }}</td>
                    <td>{{ $row->is_active == '1' ? trans('clients.active') : trans('clients.inactive') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">{{ trans('reports.no_results') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>

