@extends('dashbord.layouts.master')

@section('toolbar')
    @if(request('mobile') === 'collectors')
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.mobile_view') }}" class="btn btn-icon btn-light-primary rounded-circle w-40px h-40px">
                    <i class="bi bi-arrow-right fs-2"></i>
                </a>
                <h3 class="mb-0 fw-bold">المحصلين</h3>
            </div>
        </div>
    @else
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            @php
                $title = trans('users.users');
                $breadcrumbs = [
                    ['label' => trans('Toolbar.home'), 'link' => route('admin.users.create')],
                    ['label' => trans('Toolbar.users'), 'link' => ''],
                    ['label' => trans('users.users_table'), 'link' => ''],
                ];

                PageTitle($title, $breadcrumbs);
            @endphp


            <div class="d-flex align-items-center gap-2 gap-lg-3">

                @can('create_user')
                    {{ AddButton(route('admin.users.create')) }}
                @endcan

            </div>
        </div>
    @endif
@endsection

@section('content')

    @if(request('mobile') === 'collectors')
        <div id="kt_app_content_container" class="app-container container-xxl">
           

            <div class="card shadow-sm mb-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fs-7 text-gray-500 d-block mb-1">إجمالي المحصلين</span>
                        <span class="fs-4 fw-bold text-gray-800">{{ $collectorsCount ?? 0 }}</span>
                    </div>
                    <div class="text-end">
                        <span class="fs-7 text-gray-500 d-block mb-1">إجمالي التحصيل</span>
                        <span class="fs-4 fw-bold text-success dir-ltr">
                            {{ number_format($collectorsTotalAmount ?? 0, 2) }} {{ get_app_config_data('currency') }}
                        </span>
                    </div>
                </div>
            </div>


             @if($accountantAccount)
                <div class="card shadow-sm mb-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-45px symbol-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                <i class="bi bi-cash-coin fs-2"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-gray-900 mb-1">حساب المحاسب</div>
                                <div class="text-gray-500 fs-8">{{ $accountantAccount->name }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="text-gray-500 fs-8 mb-1">رصيد الحساب</div>
                            <div class="fw-bold text-primary fs-6 dir-ltr">
                                {{ number_format($accountantAccount->financial_transactions_sum_amount ?? 0, 2) }} {{ get_app_config_data('currency') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row g-3">
                @foreach(($collectors ?? []) as $collector)
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-3">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="symbol symbol-45px symbol-circle">
                                        <img src="{{ $collector->image }}" alt="{{ $collector->name }}" class="object-fit-cover">
                                    </div>
                                    <div>
                                        <div class="fw-bold text-gray-900 mb-1">{{ $collector->name }}</div>
                                        <div class="text-gray-500 fs-8">
                                            @if(isset($collector->roles) && $collector->roles->isNotEmpty())
                                                @php
                                                    $role = $collector->roles->first();
                                                @endphp
                                                @if(method_exists($role, 'getTranslation'))
                                                    {{ $role->getTranslation('title', app()->getLocale()) }}
                                                @elseif(isset($role->title))
                                                    {{ $role->title }}
                                                @else
                                                    {{ $role }}
                                                @endif
                                            @else
                                                محاسب
                                            @endif
                                        </div>
                                        @if($collector->account ?? null)
                                            <div class="text-gray-400 fs-7 mt-1">
                                                حساب: {{ $collector->account->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="text-gray-500 fs-8 mb-1">إجمالي التحصيل</div>
                                    <div class="fw-bold text-success fs-6 dir-ltr">
                                        {{ number_format($collector->financial_transactions_sum_amount ?? 0, 2) }} {{ get_app_config_data('currency') }}
                                    </div>
                                    @if($collector->account)
                                        <div class="text-gray-400 fs-7 mt-1">
                                            رصيد الحساب: {{ number_format($collector->account->financial_transactions_sum_amount ?? 0, 2) }} {{ get_app_config_data('currency') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div id="kt_app_content_container" class="app-container container-xxxl">

            <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
                @php
                    $headers = [
                        'users.ID',
                        'users.name',
                        'users.email',
                        'users.role',
                        'users.position',
                        'users.created_by',
                        'users.status',
                        'users.collected_amount',
                        'users.actions',
                    ];

                    generateTable($headers);
                @endphp
            </div>

        </div>
    @endif


@stop

@section('js')

    @if(request('mobile') !== 'collectors')
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
                "pageLength": 10,
                "ajax": {
                    url: "{{ route('admin.users.index') }}",
                    type: 'GET'
                },
                "columns": [{
                        data: 'id',
                        className: 'text-center no-export'
                    },
                    {
                        data: 'name',
                        className: 'text-center no-export'
                    },
                    {
                        data: 'email',
                        className: 'text-center'
                    },
                    {
                        data: 'role',
                        className: 'text-center'
                    },
                    {
                        data: 'position',
                        className: 'text-center'
                    },
                    {
                        data: 'created_by',
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        className: 'text-center'
                    },
                    {
                        data: 'collected_amount',
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
                        "targets": [7],
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
    @endif

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
    </script>

@endsection
