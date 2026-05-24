<div class="" style="margin-top: 30px;padding: 30px;">
    @if(isset($revenues_data) && !empty($revenues_data))
        <table id="table_10" class="table table-bordered responsive nowrap text-center" cellspacing="0"
               width="100%">
            <thead>
            <tr class="greentd" style="background-color: lightgrey" >
                <th>{{trans('employees.hash') }}</th>
                <th>{{ trans('employees.amount') }}</th>
                <th>{{ trans('employees.invoice') }}</th>
                <th>{{ trans('employees.client') }}</th>
                <th>{{ trans('employees.received_at') }}</th>
                <th>{{ trans('employees.notes') }}</th>
                {{-- <th>{{ trans('employees.actions') }}</th> --}}
            </tr>
            </thead>
            <tbody>
            @php
                $x = 1;
            @endphp
            @foreach ($revenues_data as $revenue)
                <tr>
                    <td>{{ $x++ }}</td>
                    <td data-order="{{ $revenue->amount }}">{{ number_format($revenue->amount, 2) }}</td>
                    <td>
                        <a href="javascript:void(0)" data-url="{{ route('admin.invoice_details', $revenue->id) }}" onclick="invoice_details(this.getAttribute('data-url'))"
                            class="text-primary fw-bold text-decoration-underline" title="{{ trans('invoices.view_details') }}">
                            INV-{{ $revenue->invoice ? $revenue->invoice->invoice_number : 'N/A' }}
                        </a>
                    </td>
                    <td>{{ $revenue->client ? $revenue->client->name : 'N/A' }}</td>
                    <td class="fnt_center_black">{{ \Illuminate\Support\Carbon::parse($revenue->received_at)->format('Y-m-d') }}</td>
                    <td class="fnt_center_blue">{{ $revenue->notes ?? 'N\A' }}</td>

                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f8f9fa; font-weight: bold;">
                    <td colspan="1" style="text-align: right;">{{ trans('employees.total') }}:</td>
                    <td id="totalAmount" style="text-align: center; font-weight: bold; color: #28a745;"></td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    @endif
</div>



<div class="modal fade" tabindex="-1" id="modaldetails">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= trans('invoices.invoice_details') ?></h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1">&times;</i>
                </div>

            </div>

            <div id="result_info">

            </div>

        </div>
    </div>
</div>


