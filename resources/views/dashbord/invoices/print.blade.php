<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trans('invoices.Invoice Details') }}</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --border-color: #eaeaea;
            --success-color: #27ae60;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
            --alternate-row: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 2rem;
            background-color: #f7f9fc;
            color: #333;
            direction: ltr;
        }

        [dir="rtl"] body {
            direction: rtl;
            text-align: right;
            font-family: 'Tajawal', sans-serif;
        }

        .invoice-container {
            max-width: 1000px;
            margin: auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .invoice-header {
            background: var(--primary-color);
            color: white;
            padding: 2rem;
            position: relative;
            text-align: left;
        }

        .invoice-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, var(--success-color), var(--warning-color));
        }

        .invoice-title {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        [dir="rtl"] .invoice-header {
            text-align: right;
        }

        .invoice-content {
            padding: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 2rem;
        }

        [dir="rtl"] .info-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            font-size: 0.875rem;
            color: var(--secondary-color);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-value {
            font-size: 1.125rem;
            color: var(--primary-color);
        }

        .details-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
        }

        .details-table th,
        .details-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        [dir="rtl"] .details-table th,
        [dir="rtl"] .details-table td {
            text-align: right;
        }

        .details-table tr:nth-child(even) {
            background-color: var(--alternate-row);
        }

        .details-table th {
            background-color: var(--secondary-color);
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
            width: 30%;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            display: inline-block;
            width: 15%;
        }

        .status-paid {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .status-partial {
            background-color: rgba(241, 196, 15, 0.1);
            color: var(--warning-color);
        }

        .status-unpaid {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        @media print {
            body {
                background-color: white !important;
                padding: 0 !important;
            }

            .invoice-container {
                box-shadow: none;
                border: 1px solid var(--border-color);
                width: 100%;
                max-width: 100%;
            }

            .invoice-header {
                background: var(--primary-color) !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .details-table {
                width: 100%;
                border-collapse: collapse !important;
                border: 1px solid var(--border-color) !important;
            }

            .details-table th,
            .details-table td {
                border: 1px solid var(--border-color) !important;
                padding: 1rem !important;
                text-align: left !important;
                font-size: 14px !important;
                -webkit-print-color-adjust: exact !important;
            }

            [dir="rtl"] .details-table th,
            [dir="rtl"] .details-table td {
                text-align: right !important;
            }

            .details-table th {
                background-color: var(--secondary-color) !important;
                color: white !important;
                font-weight: bold !important;
            }

            .details-table tr:nth-child(even) {
                background-color: var(--alternate-row) !important;
            }

            .status-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <header class="invoice-header">
            <h1 class="invoice-title">
                {{ trans('invoices.Invoice Details') }}
            </h1>
        </header>

        <div class="invoice-content">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">{{ trans('invoices.Invoice Number') }}</span>
                    <span class="info-value">{{ $all_data->invoice_number }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ trans('invoices.Client') }}</span>
                    <span class="info-value">{{ $all_data->client->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ trans('invoices.Subscription') }}</span>
                    <span class="info-value">{{ $all_data->subscription ? $all_data->subscription->name : 'خدمة' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ trans('invoices.Status') }}</span>
                    <span
                        class="status-badge {{ $all_data->status == 'paid' ? 'status-paid' : ($all_data->status == 'partial' ? 'status-partial' : 'status-unpaid') }}">
                        {{ trans('invoices.' . ($all_data->status ?? 'N/A')) }}
                    </span>
                </div>
            </div>

            <table class="details-table">
                <tr>
                    <th>{{ trans('invoices.Amount') }}</th>
                    <td>{{ $all_data->amount }}</td>
                </tr>
                <tr>
                    <th>{{ trans('invoices.Paid Amount') }}</th>
                    <td>{{ $all_data->paid_amount ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>{{ trans('invoices.Remaining Amount') }}</th>
                    <td>{{ $all_data->remaining_amount }}</td>
                </tr>
                <tr>
                    <th>{{ trans('invoices.Creation Date') }}</th>
                    <td>{{ $all_data->enshaa_date }}</td>
                </tr>
                <tr>
                    <th>{{ trans('invoices.Due Date') }}</th>
                    <td>{{ $all_data->due_date }}</td>
                </tr>
                <tr>
                    <th>{{ trans('invoices.Invoice Type') }}</th>
                    <td>{{ $all_data->invoice_type }}</td>
                </tr>
                <tr>
                    <th>{{ trans('invoices.Employee') }}</th>
                    <td>{{ $all_data->employee ? $all_data->employee->first_name . ' ' . $all_data->employee->last_name : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <th>{{ trans('invoices.Notes') }}</th>
                    <td>{{ $all_data->notes ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
<script>
    window.onload = function() {
        window.print();
    }
</script>

</html>
