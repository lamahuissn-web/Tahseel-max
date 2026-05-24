@extends('dashbord.layouts.master')

@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('account_transfers.account_transfers');
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => ''],
                ['label' => trans('Toolbar.account_transfers'), 'link' => ''],
                ['label' => trans('account_transfers.account_transfers_table'), 'link' => ''],
            ];

            PageTitle($title, $breadcrumbs);
        @endphp

        @can('create_account_transfer')
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAccountTransfers">
                    {{ trans('account_transfers.add_transfer') }}
                </button>
            </div>
        @endcan
    </div>
@endsection

@section('content')
    <div id="kt_app_content_container" class="app-container container-xxxl">
        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            @php
                $headers = [
                    'account_transfers.ID',
                    'account_transfers.from_account',
                    'account_transfers.to_account',
                    'account_transfers.amount',
                    'account_transfers.date',
                    'account_transfers.notes',
                    'account_transfers.created_by',
                    'account_transfers.actions',
                ];

                generateTable($headers);
            @endphp
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="modalAccountTransfers">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">{{ trans('account_transfers.add_transfer') }}</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.add_account_transfer') }}" enctype="multipart/form-data"
                    id="accountTransferForm">
                    @csrf
                    <input type="hidden" name="row_id" id="row_id" value="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="from_account"
                                class="form-label">{{ trans('account_transfers.from_account') }}</label>
                            <select name="from_account" id="from_account" class="form-control" required>
                                <option value="">{{ trans('account_transfers.select_account') }}</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="to_account" class="form-label">{{ trans('account_transfers.to_account') }}</label>
                            <select name="to_account" id="to_account" class="form-control" required>
                                <option value="">{{ trans('account_transfers.select_account') }}</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @if($account->id == $masrofatAccountId) data-is-masrofat="true" @endif>{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3" id="bandSection" style="display: none;">
                            <label for="band_id" class="form-label">{{ trans('account_transfers.band') }}</label>
                            <select name="band_id" id="band_id" class="form-control">
                                @foreach ($bands as $band)
                                    <option value="{{ $band->id }}" {{ old('band_id') == $band->id ? 'selected' : '' }}>{{ $band->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">{{ trans('account_transfers.amount') }}</label>
                            <input type="number" class="form-control" name="amount" id="amount" required>
                            <div id="amount_warning" class="text-danger mt-1 small" style="display: none;">
                                <i class="bi bi-exclamation-triangle"></i>
                                {{ trans('account_transfers.amount_exceeds_balance') }}
                            </div>
                        </div>
                        {{-- <div class="mb-3">
                            <label for="date" class="form-label">{{ trans('account_transfers.date') }}</label>
                            <input type="date" class="form-control" name="date" id="date">
                        </div>
                        <div class="mb-3">
                            <label for="time" class="form-label">{{ trans('account_transfers.time') }}</label>
                            <input type="time" class="form-control" name="time" id="time">
                        </div> --}}
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ trans('account_transfers.notes') }}</label>
                            <textarea class="form-control" name="notes" id="notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{ trans('account_transfers.save') }}</button>
                        <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">{{ trans('account_transfers.cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Datatables initialization
            table = $('#table1').DataTable({
                "language": {
                    url: "{{ asset('assets/Arabic.json') }}"
                },
                "processing": true,
                "serverSide": true,
                "order": [],
                "pageLength": 10,
                "ajax": {
                    url: "{{ route('admin.get_ajax_account_transfers') }}",
                    type: 'GET'
                },
                "columns": [{
                        data: 'id',
                        className: 'text-center'
                    },
                    {
                        data: 'from_account',
                        className: 'text-center'
                    },
                    {
                        data: 'to_account',
                        className: 'text-center'
                    },
                    {
                        data: 'amount',
                        className: 'text-center'
                    },
                    {
                        data: 'date',
                        className: 'text-center'
                    },
                    {
                        data: 'notes',
                        className: 'text-center'
                    },
                    {
                        data: 'created_by',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        className: 'text-center no-export'
                    },
                ],
                "columnDefs": [{
                        "targets": [1, -1],
                        "orderable": false
                    },
                    {
                        "targets": [1],
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).css({
                                'font-weight': '600',
                                'text-align': 'center',
                                'color': '#6610f2',
                                'vertical-align': 'middle',
                            });
                        }
                    },
                    {
                        "targets": [3, 4],
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).css({
                                'font-weight': '600',
                                'text-align': 'center',
                                'vertical-align': 'middle',
                            });
                        }
                    },
                    {
                        "targets": [2],
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).css({
                                'font-weight': '600',
                                'text-align': 'center',
                                'color': 'green',
                                'vertical-align': 'middle',
                            });
                        }
                    },
                    {
                        "targets": [5],
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).css({
                                'font-weight': '600',
                                'text-align': 'center',
                                'color': 'red',
                                'vertical-align': 'middle',
                            });
                        }
                    },
                ],
                "dom": '<"row align-items-center"<"col-md-3"l><"col-md-6"f><"col-md-3"B>>rt<"row align-items-center"<"col-md-6"i><"col-md-6"p>>',
                "buttons": [{
                        "extend": 'excel',
                    },
                    {
                        "extend": 'copy',
                    },
                ],
                "lengthMenu": [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "الكل"]
                ],
            });

            $("input, textarea, select").change(function() {
                $(this).parent().parent().removeClass('has-error');
                $(this).next().empty();
            });
        });

        // function editAccountTransfer(id) {
        //     $.ajax({
        //         url: "{{ route('admin.edit_account_transfer', ['id' => '__id__']) }}".replace('__id__', id),
        //         type: "get",
        //         dataType: "json",
        //         success: function(data) {
        //             $('#row_id').val(data.transfer.id);
        //             $('#from_account').val(data.transfer.from_account);
        //             $('#to_account').val(data.transfer.to_account);
        //             $('#amount').val(data.transfer.amount);
        //             $('#date').val(data.transfer.date);
        //             $('#time').val(data.transfer.time);
        //             $('#notes').val(data.transfer.notes);
        //         },
        //     });
        // }
    </script>

    <script>
        $(document).ready(function() {
            var currentBalance = 0;

            $('#to_account').change(function() {
                var selectedOption = $(this).find('option:selected');
                var isMasrofatAccount = selectedOption.data('is-masrofat');

                if (isMasrofatAccount) {
                    $('#bandSection').show();
                    $('#band_id').prop('required', true);
                } else {
                    $('#bandSection').hide();
                    $('#band_id').prop('required', false);
                }
            });

            $('#from_account').change(function() {
                var id = $(this).val();
                if (id) {
                    $.ajax({
                        url: "{{ route('admin.get_account_balance', ['id' => '__id__']) }}".replace('__id__', id),
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            currentBalance = data.balance;
                            $('#amount').val(data.balance);
                            $('#amount').attr('max', data.balance);
                        }
                    });
                } else {
                    currentBalance = 0;
                    $('#amount').val('');
                    $('#amount').removeAttr('max');
                }
            });

            $(document).on('input', '#amount', function() {
                let enteredAmount = parseFloat($(this).val()) || 0;

                if (enteredAmount > currentBalance) {
                    $(this).val(currentBalance);
                    showAmountWarning();
                }
            });

            function showAmountWarning() {
                $('#amount_warning').show();

                setTimeout(() => {
                    $('#amount_warning').fadeOut();
                }, 3000);
            }
        });
    </script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\account\AccountTransferRequest', '#accountTransferForm') !!}
@endsection
