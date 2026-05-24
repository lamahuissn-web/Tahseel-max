<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Details</title>
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 2rem;
            background-color: #f7f9fc;
            color: #333;
        }

        .invoice-container {
            max-width: 1000px;
            margin: 0 auto;
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

        .details-table tr:nth-child(even) {
            background-color: var(--alternate-row);
        }

        .details-table th,
        .details-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            width: 70%;
        }

        .details-table th {
            background-color: #f8fafc;
            font-weight: 600;
            /* color: var(--secondary-color); */
            color: white;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
            width: 30%;
            background-color: var(--secondary-color);
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

        .amount-value {
            font-family: 'Monaco', 'Courier New', monospace;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            body {
                padding: 1rem;
            }

            .invoice-content {
                padding: 1rem;
            }

        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <header class="invoice-header">
            <h1 class="invoice-title">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
                Invoice Details
            </h1>
        </header>

        <div class="invoice-content">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Invoice Number</span>
                    <span class="info-value">{{ $all_data->invoice_number }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Client</span>
                    <span class="info-value">{{ $all_data->client->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Subscription</span>
                    <span class="info-value">{{ $all_data->subscription->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="status-badge {{ $all_data->status == 'paid' ? 'status-paid' : ($all_data->status == 'partial' ? 'status-partial' : 'status-unpaid') }}">
                        {{ trans('invoices.' . ($all_data->status ?? 'N/A')) }}
                    </span>
                </div>
            </div>

            <table class="details-table">
                <tr>
                    <th>Amount</th>
                    <td class="amount-value">{{ $all_data->amount }}</td>
                </tr>
                <tr>
                    <th>Remaining Amount</th>
                    <td class="amount-value">{{ $all_data->remaining_amount }}</td>
                </tr>
                <tr>
                    <th>Creation Date</th>
                    <td>{{ $all_data->enshaa_date }}</td>
                </tr>
                <tr>
                    <th>Due Date</th>
                    <td>{{ $all_data->due_date }}</td>
                </tr>
                <tr>
                    <th>Invoice Type</th>
                    <td>{{ $all_data->invoice_type }}</td>
                </tr>
                <tr>
                    <th>Employee</th>
                    <td>{{ $all_data->employee ? $all_data->employee->first_name.' '.$all_data->employee->last_name : 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Notes</th>
                    <td>{{ $all_data->notes ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
