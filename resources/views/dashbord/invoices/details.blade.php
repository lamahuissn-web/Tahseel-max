<div class="card shadow  bg-white rounded">
    <div class="card-header" style="background-color: #f8f9fa;">
        <h3 class="card-title"><i class="fas fa-text-width"></i> <?= trans('invoices.invoice_details') ?></h3>
    </div>
    <div class="card-body" style="padding: 20px !important;">
        <table class="table table-bordered table-sm table-striped" >
            <tbody>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.invoice_number') ?></td>
                    <td class="class_result">{{ $all_data->invoice_number ?? 'N\A' }}</td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.client') ?></td>
                    <td class="class_result">{{ $all_data->client->name ?? 'N\A' }}</td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.client_type') ?></td>
                    <td class="class_result">{{ trans('invoices.' . $all_data->client->client_type )}}</td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.subscription') ?></td>
                    <td class="class_result">{{ $all_data->subscription ? $all_data->subscription->name : 'خدمة' }}</td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.amount') ?></td>
                    <td class="class_result">{{ $all_data->amount ?? 'N\A' }}</td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.paid_amount') ?></td>
                    {{-- <td class="class_result">{{ $all_data->amount - $all_data->remaining_amount }}</td> --}}
                    <td class="class_result">{{ $all_data->paid_amount ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.remaining_amount') ?></td>
                    <td class="class_result">{{ $all_data->remaining_amount ?? 'N\A' }}</td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.enshaa_date') ?></td>
                    <td class="class_result">{{ $all_data->enshaa_date ?? 'N\A' }}</td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.paid_date') ?></td>
                    <td class="class_result">{{ $all_data->paid_date ? \Illuminate\Support\Carbon::parse($all_data->paid_date)->format('Y-m-d h:i A') : 'N\A'}}</td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.due_date') ?></td>
                    <td class="class_result">{{ $all_data->due_date ?? 'N\A' }}</td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%">{{ trans('invoices.status') }}</td>
                    <td>
                        <span class="badge
                            @if($all_data->status == 'paid') bg-success text-white
                            @elseif($all_data->status == 'partial') bg-warning text-dark
                            @else bg-danger text-white
                            @endif
                            px-4 py-3 rounded-pill fw-bold fs-5">
                            {{ trans('invoices.' . ($all_data->status ?? 'N/A')) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.invoice_type') ?></td>
                    <td class="class_result">{{ $all_data->invoice_type }}</td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.employee') ?></td>
                    <td class="class_result">{{ $all_data->employee ? $all_data->employee->first_name.' '.$all_data->employee->last_name : 'N\A' }}</td>
                </tr>
                <tr>
                    <td class="class_label" style="width: 25%"><?= trans('invoices.notes') ?></td>
                    <td class="class_result">{{ $all_data->notes ?? 'N\A'}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
