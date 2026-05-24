@extends('dashbord.layouts.master')

@section('css')
    <style>
        .account-row.level-1 {
            background-color: #f8f9fa;
        }

        .account-row.level-2 {
            background-color: #e9ecef;
        }
    </style>
@endsection
@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('accounts.accounts');
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => ''],
                ['label' => trans('Toolbar.accounts'), 'link' => ''],
                ['label' => trans('accounts.accounts_table'), 'link' => ''],
            ];

            PageTitle($title, $breadcrumbs);
        @endphp

        @can('create_account')
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAccounts">
                    {{ trans('accounts.add_account') }}
                </button>
            </div>
        @endcan
    </div>
@endsection

@section('content')
    <div id="kt_app_content_container" class="app-container container-xxxl">
        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            <div class="card-body">
                <h2>{{ trans('accounts.accounts') }}</h2>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('accounts.ID') }}</th>
                            <th>{{ trans('accounts.account_name') }}</th>
                            <th>{{ trans('accounts.parent') }}</th>
                            <th>{{ trans('accounts.level') }}</th>
                            <th>{{ trans('accounts.sum_amount') }}</th>
                            {{-- <th>{{ trans('accounts.assigned_user') }}</th> --}}
                            <th>{{ trans('accounts.created_by') }}</th>
                            <th>{{ trans('accounts.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accounts as $account)
                            @include('dashbord.accounts.partials.account_row', [
                                'account' => $account,
                            ])
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="modalAccounts">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">{{ trans('accounts.add_account') }}</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.add_account') }}" enctype="multipart/form-data"
                    id="accountForm">
                    @csrf
                    <input type="hidden" name="row_id" id="row_id" value="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ trans('accounts.account_name') }}</label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">{{ trans('accounts.parent') }}</label>
                            <select name="parent_id" id="parent_id" class="form-control">
                                <option value="">{{ trans('accounts.select_account') }}</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{ trans('accounts.save') }}</button>
                        <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">{{ trans('accounts.cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="transactionsModal" tabindex="-1" aria-labelledby="transactionsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionsModalLabel">{{ trans('accounts.transactions_for') }} <span
                            id="accountName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table1" class="table table-bordered">
                                <thead>
                                    <tr class="fw-bold fs-6 text-gray-800">
                                        <th style="text-align: center;">{{ trans('accounts.ID') }}</th>
                                        <th style="text-align: center;">{{ trans('accounts.amount') }}</th>
                                        <th style="text-align: center;">{{ trans('accounts.account') }}</th>
                                        <th style="text-align: center;">{{ trans('accounts.date') }}</th>
                                        <th style="text-align: center;">{{ trans('accounts.time') }}</th>
                                        <th style="text-align: center;">{{ trans('accounts.type') }}</th>
                                        <th style="text-align: center;">{{ trans('accounts.notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="transactionsTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center">No transactions found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        function confirmDelete(clientId) {
            Swal.fire({
                title: '{{ trans('employees.confirm_delete') }}',
                text: '{{ trans('clients.delete_warning') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ trans('employees.yes_delete') }}',
                cancelButtonText: '{{ trans('employees.cancel') }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + clientId).submit();
                }
            });
        }

        function edit_account(id) {
            $.ajax({
                url: "{{ route('admin.edit_account', ['id' => '__id__']) }}".replace('__id__', id),
                type: "get",
                dataType: "json",
                success: function(data) {
                    var allData = data.all_data;
                    $('#row_id').val(allData.id);
                    $('#name').val(allData.name);
                    $('#parent_id').val(allData.parent_id);
                },
            });
        }

        function viewTransactions(id) {
            $.ajax({
                url: "{{ route('admin.accounts_transactions', ['id' => '__id__']) }}".replace('__id__', id),
                type: "GET",
                dataType: "json",
                success: function(response) {
                    $('#accountName').text(response.account_name);
                    var transactionsTableBody = $('#transactionsTableBody');
                    transactionsTableBody.empty();

                    if (response.transactions.length > 0) {
                        response.transactions.forEach(function(transaction) {
                            transactionsTableBody.append(`
                                <tr>
                                    <td class="text-center">${transaction.id}</td>
                                    <td class="text-center">${transaction.amount}</td>
                                    <td class="text-center">${transaction.account?.name ?? 'N/A'}</td>
                                    <td class="text-center">${transaction.date}</td>
                                    <td class="text-center">${transaction.time ?? ''}</td>
                                    <td class="text-center">${transaction.type == 'qapd' ? 'قبض' : 'صرف'}</td>
                                    <td class="text-center">${transaction.notes || ''}</td>
                                </tr>
                            `);
                        });
                    } else {
                        transactionsTableBody.append(`
                            <tr>
                                <td colspan="8" class="text-center">{{ trans('accounts.no_transactions') }}</td>
                            </tr>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    alert('Failed to load transactions. Check the console for details.');
                }
            });

            $('#transactionsModal').modal('show');
        }
    </script>


    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\account\SaveRequest', '#accountForm') !!}
@endsection
