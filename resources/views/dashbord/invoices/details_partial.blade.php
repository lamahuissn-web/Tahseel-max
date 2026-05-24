<table>
    <tr>
        <td>{{ trans('invoices.invoice_number') }}</td>
        <td>{{ $invoice->invoice_number ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>{{ trans('invoices.client') }}</td>
        <td>{{ $invoice->client->name ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>{{ trans('invoices.subscription') }}</td>
        <td>{{ $invoice->subscription ? $invoice->subscription->name : trans('invoices.service') }}</td>
    </tr>
    <tr>
        <td>{{ trans('invoices.amount') }}</td>
        <td>{{ number_format($invoice->amount, 2) }}</td>
    </tr>
    <tr>
        <td>{{ trans('invoices.paid_amount') }}</td>
        <td>{{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
    </tr>
    <tr>
        <td>{{ trans('invoices.remaining_amount') }}</td>
        <td style="color:#dc3545;font-weight:700;">{{ number_format($invoice->remaining_amount, 2) }}</td>
    </tr>
    <tr>
        <td>{{ trans('invoices.enshaa_date') }}</td>
        <td>{{ $invoice->enshaa_date ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>{{ trans('invoices.due_date') }}</td>
        <td>{{ $invoice->due_date ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td>{{ trans('invoices.paid_date') }}</td>
        <td>{{ $invoice->paid_date ? \Carbon\Carbon::parse($invoice->paid_date)->format('Y-m-d h:i A') : 'N/A' }}</td>
    </tr>
    <tr>
        <td>{{ trans('invoices.status') }}</td>
        <td>
            @if($invoice->status == 'paid')
                <span style="color:#198754;font-weight:600;">{{ trans('invoices.paid') }}</span>
            @elseif($invoice->status == 'partial')
                <span style="color:#856404;font-weight:600;">{{ trans('invoices.partial') }}</span>
            @else
                <span style="color:#dc3545;font-weight:600;">{{ trans('invoices.unpaid') }}</span>
            @endif
        </td>
    </tr>
    <tr>
        <td>{{ trans('invoices.invoice_type') }}</td>
        <td>{{ $invoice->invoice_type }}</td>
    </tr>
    <tr>
        <td>{{ trans('invoices.employee') }}</td>
        <td>{{ $invoice->employee ? $invoice->employee->first_name . ' ' . $invoice->employee->last_name : 'N/A' }}</td>
    </tr>
    @if($invoice->notes)
    <tr>
        <td>{{ trans('invoices.notes') }}</td>
        <td>{{ $invoice->notes }}</td>
    </tr>
    @endif
</table>
