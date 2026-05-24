@extends('dashbord.layouts.master')

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


        <div class="d-flex align-items-center gap-2 gap-lg-3">

            {{-- @can('create_account') --}}
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAccounts">
                {{ trans('accounts.add_account') }}
            </button>
            {{-- @endcan --}}

        </div>
    </div>

@endsection
@section('content')

    <div id="kt_app_content_container" class="app-container container-xxxl">

        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            @php
                $headers = [
                    'accounts.ID',
                    'accounts.account_name',
                    'accounts.parent',
                    'accounts.level',
                    'accounts.assigned_user',
                    'accounts.created_by',
                    'accounts.actions',
                ];

                generateTable($headers);
            @endphp
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
                        {{-- <div class="mb-3">
                            <label for="user_id" class="form-label">{{ trans('accounts.assigned_user') }}</label>
                            <select name="user_id" id="user_id" class="form-control">
                                <option value="">{{ trans('accounts.select_user') }}</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div> --}}
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
@stop
@section('js')

    <script>
        $(document).ready(function() {
            //datatables
            table = $('#table1').DataTable({
                "language": {
                    url: "{{ asset('assets/Arabic.json') }}"
                },
                "processing": true,
                "serverSide": true,
                "order": [],
                "ajax": {
                    url: "{{ route('admin.get_ajax_accounts') }}",
                    type: 'GET'
                },
                "columns": [{
                        data: 'id',
                        className: 'text-center'
                    },
                    {
                        data: 'name',
                        className: 'text-center'
                    },
                    {
                        data: 'parent_account',
                        className: 'text-center'
                    },
                    {
                        data: 'level',
                        className: 'text-center'
                    },
                    {
                        data: 'assigned_user',
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
                        "targets": [1, -1], //last column
                        "orderable": false, //set not orderable
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
                "order": [],
                "dom": '<"row align-items-center"<"col-md-3"l><"col-md-6"f><"col-md-3"B>>rt<"row align-items-center"<"col-md-6"i><"col-md-6"p>>',
                "buttons": [{
                        "extend": 'excel',
                    },
                    {
                        "extend": 'copy',
                    },
                    {
                        "extend": 'pdf'
                    }
                ],

                "language": {
                    "lengthMenu": "عرض _MENU_ سجلات",
                    "zeroRecords": "لا توجد سجلات",
                    "info": "عرض الصفحة _PAGE_ من _PAGES_",
                    "infoEmpty": "لا توجد سجلات",
                    "infoFiltered": "(مرشح من _MAX_ إجمالي السجلات)",
                    "search": "بحث:",
                    "paginate": {
                        "first": "الأول",
                        "last": "الأخير",
                        "next": "التالي",
                        "previous": "السابق"
                    }
                },
                "lengthMenu": [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "الكل"]
                ],
            });

            $("input").change(function() {
                $(this).parent().parent().removeClass('has-error');
                $(this).next().empty();
            });
            $("textarea").change(function() {
                $(this).parent().parent().removeClass('has-error');
                $(this).next().empty();
            });
            $("select").change(function() {
                $(this).parent().parent().removeClass('has-error');
                $(this).next().empty();
            });
        });
    </script>

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
                    // console.log(data);
                    var allData = data.all_data;
                    $('#row_id').val(allData.id);
                    $('#name').val(allData.name);
                    $('#parent_id').val(allData.parent_id);
                    // $('#user_id').val(allData.account_id);
                    // $('#user_id').val(data.account_id).trigger('change');
                },
            });
        }
    </script>
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\account\SaveRequest', '#accountForm') !!}
@endsection
