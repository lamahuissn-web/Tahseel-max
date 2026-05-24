@extends('dashbord.layouts.master')

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('masrofat.masrofat');
    $breadcrumbs = [
    ['label' => trans('Toolbar.home'), 'link' => route('admin.masrofat.create')],
    ['label' => trans('Toolbar.masrofat'), 'link' => ''],
    ['label' => trans('masrofat.masrofat_table'), 'link' => ''],
    ];

    PageTitle($title, $breadcrumbs);
    @endphp


    {{-- <div class="d-flex align-items-center gap-2 gap-lg-3">

            @can('create_masrofat')
                {{ AddButton(route('admin.masrofat.create')) }}
    @endcan

</div> --}}
</div>

@endsection
@section('content')

<div id="kt_app_content_container" class="app-container container-xxxl">
    <div class="card shadow-sm" style="margin-bottom: 20px;">
        <div class="card-body">
            <div class="col-md-12 row">

                <div class="col-md-3">
                    <label for="band_id" class="form-label">{{ trans('reports.band') }}</label>
                    <div class="input-group flex-nowrap ">
                        <span class="input-group-text" id="basic-addon3">{!! form_icon('select1') !!}</i></span>
                        <div class="overflow-hidden flex-grow-1">
                            <select class="form-select rounded-start-0" name="band_id" id="band_id"
                                data-placeholder="{{ trans('reports.select') }}">
                                <option value="">{{ trans('reports.select') }}</option>
                                @foreach ($bands as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('band_id') == $item->id ? 'selected' : '' }}>{{ $item->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @error('band_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="from_date" class="form-label">{{ trans('reports.from_date') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('date') !!}</span>
                        <input type="date" class="form-control" name="from_date" id="from_date"
                            value="{{ old('from_date') }}">
                    </div>
                    @error('from_date')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="to_date" class="form-label">{{ trans('reports.to_date') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('date') !!}</span>
                        <input type="date" class="form-control" name="to_date" id="to_date"
                            value="{{ old('to_date') }}">
                    </div>
                    @error('to_date')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="value" class="form-label">{{ trans('masrofat.value') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('price') !!}</span>
                        <input type="text" class="form-control" name="value" id="value"
                            placeholder="{{ trans('masrofat.value') }}" value="{{ old('value') }}">
                    </div>
                    @error('value')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="notes" class="form-label">{{ trans('masrofat.notes') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('text') !!}</span>
                        <input type="text" class="form-control" name="notes" id="notes"
                            placeholder="{{ trans('masrofat.notes') }}" value="{{ old('notes') }}">
                    </div>
                    @error('notes')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="created_by" class="form-label">{{ trans('masrofat.created_by') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('text') !!}</span>
                        <input type="text" class="form-control" name="created_by" id="created_by"
                            placeholder="{{ trans('masrofat.created_by') }}" value="{{ old('created_by') }}">
                    </div>
                    @error('created_by')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
        @php
        $headers = [
        'masrofat.ID',
        // 'masrofat.emp_name',
        'masrofat.band_name',
        'masrofat.value',
        'masrofat.notes',
        'masrofat.created_at',
        'masrofat.created_by',
        // 'masrofat.actions',
        ];

        generateTable($headers);
        @endphp
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
                url: "{{ route('admin.masrofat.index') }}",
                type: 'GET',
                data: function(d) {
                    d.band_id = $('#band_id').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.value = $('#value').val();
                    d.notes = $('#notes').val();
                    d.created_by = $('#created_by').val();
                }
            },
            "columns": [{
                    data: 'id',

                    className: 'text-center no-export'
                },
                // {
                //     data: 'emp_id',
                //     className: 'text-center no-export'
                // },
                {
                    data: 'band_id',
                    className: 'text-center'
                },
                {
                    data: 'value',
                    className: 'text-center'
                },
                {
                    data: 'notes',
                    className: 'text-center'
                },
                {
                    data: 'created_at',
                    className: 'text-center'
                },
                {
                    data: 'created_by',
                    className: 'text-center'
                },
                // {
                //     data: 'action',
                //     name: 'action',
                //     orderable: false,
                //     className: 'text-center no-export'
                // },
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
    $('#band_id, #from_date, #to_date').change(function() {
        table.ajax.reload();
    });

    var delayTimer;
    $('#value, #notes, #created_by').keyup(function() {
        clearTimeout(delayTimer);
        delayTimer = setTimeout(function() {
            table.ajax.reload();
        }, 100);
    });

    function confirmDelete(clientId) {
        Swal.fire({
            title: '{{ trans('
            employees.confirm_delete ') }}',
            text: '{{ trans('
            clients.delete_warning ') }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '{{ trans('
            employees.yes_delete ') }}',
            cancelButtonText: '{{ trans('
            employees.cancel ') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + clientId).submit();
            }
        });
    }
</script>

@endsection