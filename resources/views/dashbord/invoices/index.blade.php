@extends('dashbord.layouts.master')


@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('invoices.invoices');
    $breadcrumbs = [
    ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
    ['label' => trans('Toolbar.invoices'), 'link' => ''],
    ['label' => trans('invoices.invoices_table'), 'link' => ''],
    ];

    PageTitle($title, $breadcrumbs);
    @endphp


    <div class="d-flex align-items-center gap-2 gap-lg-3">

        <form action="{{ route('admin.invoices_generate') }}" method="POST" class="text-center">
            @csrf
            @php
            $invoicesGenerated = App\Models\Admin\MonthlyInvoiceGeneration::where('year_month', now()->format('Y-m'))->exists();
            @endphp

            <div class="d-flex flex-column align-items-center">
                <button type="submit"
                    class="btn btn-primary btn-sm d-flex align-items-center gap-2 ms-4 {{ $invoicesGenerated ? 'disabled' : '' }}"
                    onclick="{{ !$invoicesGenerated ? "return confirm('هل انت متأكد من انشاء الفواتير لهذا الشهر؟')" : '' }}"
                    {{ $invoicesGenerated ? 'disabled' : '' }}>
                    <span class="svg-icon svg-icon-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                transform="rotate(-90 11.364 20.364)" fill="currentColor" />
                            <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="currentColor" />
                        </svg>
                    </span>
                    <span>{{ trans('invoices.generate_invoices') }}</span>
                </button>
            </div>
            @if($invoicesGenerated)
            <small class="text-muted mt-1 ms-4" style="font-size: 0.75rem;">
                (تم إنشاء الفواتير لهذا الشهر)
            </small>
            @endif
        </form>
    </div>
</div>

@endsection
@section('content')

<div id="kt_app_content_container" class="app-container container-xxxl">
    <div class="card shadow-sm" style="margin-bottom: 20px;">
        <div class="card-body">
            <div class="col-md-12 row">
                <div class="col-md-3">
                    <label for="client_id" class="form-label">{{ trans('reports.client_id') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('select1') !!}</span>
                        <select class="form-select filter-select selectpicker" name="client_id" id="client_id" data-live-search="true">
                            <option value="">{{ trans('reports.select') }}</option>
                            @foreach ($clients as $item)
                            <option value="{{ $item->id }}" {{ old('client_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="subscription_id" class="form-label">{{ trans('reports.subscription') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('select1') !!}</span>
                        <select class="form-select filter-select" name="subscription_id" id="subscription_id">
                            <option value="">{{ trans('reports.select') }}</option>
                            @foreach($subscriptions as $subscription)
                            <option value="{{ $subscription->id }}">{{ $subscription->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="collector_id" class="form-label">{{ trans('reports.collector') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('select1') !!}</span>
                        <select class="form-select filter-select" name="collector_id" id="collector_id">
                            <option value="">{{ trans('reports.select') }}</option>
                            @foreach($collectors as $collector)
                            <option value="{{ $collector->id }}">{{ $collector->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">{{ trans('reports.status') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text" id="basic-addon4">{!! form_icon('select1') !!}</span>
                        <select class="form-select filter-select" name="status" id="status">
                            <option value="">
                                {{ trans('reports.select') }}
                            </option>
                            <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>
                                {{ trans('reports.paid') }}
                            </option>
                            <option value="partial" {{ old('status') == 'partial' ? 'selected' : '' }}>
                                {{ trans('reports.partial') }}
                            </option>
                            <option value="unpaid" {{ old('status') == 'unpaid' ? 'selected' : '' }}>
                                {{ trans('reports.unpaid') }}
                            </option>
                        </select>
                    </div>
                    @error('status')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3" style="margin-top: 10px;">
                    <label for="min_amount" class="form-label">{{ trans('reports.min_amount') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('money') !!}</span>
                        <input type="number" class="form-control filter-input" name="min_amount" id="min_amount"
                            placeholder="{{ trans('reports.min_amount') }}">
                    </div>
                </div>

                <div class="col-md-3" style="margin-top: 10px;">
                    <label for="max_amount" class="form-label">{{ trans('reports.max_amount') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('money') !!}</span>
                        <input type="number" class="form-control filter-input" name="max_amount" id="max_amount"
                            placeholder="{{ trans('reports.max_amount') }}">
                    </div>
                </div>

                <div class="col-md-3 mb-3" style="margin-top: 10px">
                    <label for="from_date" class="form-label">{{ trans('reports.from_date') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('date') !!}</span>
                        <input type="date" class="form-control filter-input" name="from_date" id="from_date"
                            value="{{ old('from_date') }}">
                    </div>
                    @error('from_date')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" style="margin-top: 10px;">
                    <label for="to_date" class="form-label">{{ trans('reports.to_date') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('date') !!}</span>
                        <input type="date" class="form-control filter-input" name="to_date" id="to_date"
                            value="{{ old('to_date') }}">
                    </div>
                    @error('to_date')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3" style="margin-top: 10px;">
                    <label for="month_filter" class="form-label">الشهر (تاريخ الاستحقاق)</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('date') !!}</span>
                        <input type="month" class="form-control filter-input" name="month_filter" id="month_filter"
                            value="{{ old('month_filter') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="client_type" class="form-label">{{ trans('clients.client_type') }}</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('select1') !!}</span>
                        <select class="form-select filter-select" name="client_type" id="client_type">
                            <option value="">{{ trans('reports.select') }}</option>
                            <option value="internet">{{ trans('clients.internet') }}</option>
                            <option value="satellite">{{ trans('clients.satellite') }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3" >
                    <label for="sort_by" class="form-label">الترتيب حسب</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('select1') !!}</span>
                        <select class="form-select filter-select" name="sort_by" id="sort_by">
                            <option value="id" {{ old('sort_by', 'id') == 'id' ? 'selected' : '' }}>رقم الفاتورة</option>
                            <option value="due_date" {{ old('sort_by') == 'due_date' ? 'selected' : '' }}>تاريخ الاستحقاق</option>
                            <option value="paid_date" {{ old('sort_by') == 'paid_date' ? 'selected' : '' }}>تاريخ الدفع</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3" >
                    <label for="sort_order" class="form-label">نوع الترتيب</label>
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text">{!! form_icon('select1') !!}</span>
                        <select class="form-select filter-select" name="sort_order" id="sort_order">
                            <option value="desc" {{ old('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>تنازلي (الأحدث أولاً)</option>
                            <option value="asc" {{ old('sort_order') == 'asc' ? 'selected' : '' }}>تصاعدي (الأقدم أولاً)</option>
                        </select>
                    </div>
                </div>

                <!-- Reset Button Only -->
                <div class="col-md-12 mt-3" style="margin-bottom: 10px;">
                    <button type="button" id="reset-btn" class="btn btn-secondary">
                        <i class="bi bi-redo"></i> {{ trans('reports.reset') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
        @php
        $headers = [
        'invoices.ID',
        'invoices.invoice_number',
        'invoices.client',
        'invoices.client_type',
        'invoices.amount',
        'invoices.paid_amount',
        'invoices.remaining_amount',
        'invoices.due_date',
        'invoices.paid_date',
        'invoices.collected_by',
        'invoices.status',
        'invoices.subscription',
        'invoices.notes',
        'invoices.action',
        ];

        generateTable($headers);
        @endphp
    </div>
</div>


<div class="modal fade" id="payInvoiceModal" tabindex="-1" aria-labelledby="payInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payInvoiceModalLabel">{{ trans('invoices.enter_payment_amount') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="payInvoiceForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="invoice_amount" class="form-label">{{ trans('invoices.invoice_amount') }}</label>
                        <input type="number" class="form-control" id="invoice_amount" name="invoice_amount" required
                            min="1">
                    </div>
                    <div class="mb-3">
                        <label for="paid_amount" class="form-label">{{ trans('invoices.invoice_paid_amount') }}</label>
                        <input type="number" class="form-control" id="paid_amount" name="paid_amount">
                        <small class="text-muted">{{ trans('invoices.remaining_amount') }}: <span id="remaining_amount_note"></span></small>
                    </div>
                    <div class="mb-3">
                        <label for="paid_date" class="form-label">{{ trans('invoices.paid_date') }}</label>
                        <input type="date" class="form-control" id="paid_date" name="paid_date">
                        <small class="text-muted">{{ trans('invoices.optional_paid_date_update') }}</small>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ trans('invoices.notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ trans('invoices.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ trans('invoices.pay') }}</button>
                </div>
            </form>
        </div>
    </div>
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

@stop
@section('js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>


<script>
    $(function() {
        $('.selectpicker').selectpicker();
    });
</script>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#table1').DataTable({
            "language": {
                url: "{{ asset('assets/Arabic.json') }}"
            },
            "processing": true,
            "serverSide": true,
            "order": [],
            "deferRender": true,
            "stateSave": false,
            "pagingType": "simple_numbers",
            "pageLength": 10,
            "ajax": {
                url: "{{ route('admin.invoices.index') }}",
                type: "POST",
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                    // Add filter parameters to the request
                    d.client_id = $('#client_id').val();
                    d.subscription_id = $('#subscription_id').val();
                    d.collector_id = $('#collector_id').val();
                    d.status = $('#status').val();
                    d.min_amount = $('#min_amount').val();
                    d.max_amount = $('#max_amount').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.month_filter = $('#month_filter').val();
                    d.client_type = $('#client_type').val();
                    d.sort_by = $('#sort_by').val();
                    d.sort_order = $('#sort_order').val();
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            },
            "columns": [{
                    data: 'id',
                    className: 'text-center no-export'
                },
                {
                    data: 'invoice_number',
                    className: 'text-center'
                },
                {
                    data: 'client',
                    className: 'text-center'
                },
                {
                    data: 'client_type',
                    className: 'text-center'
                },
                {
                    data: 'amount',
                    className: 'text-center'
                },
                {
                    data: 'paid_amount',
                    className: 'text-center'
                },
                {
                    data: 'remaining_amount',
                    className: 'text-center'
                },
                {
                    data: 'due_date',
                    className: 'text-center'
                },
                {
                    data: 'paid_date',
                    className: 'text-center'
                },
                {
                    data: 'collected_by',
                    className: 'text-center'
                },
                {
                    data: 'status',
                    className: 'text-center'
                },
                {
                    data: 'subscription',
                    className: 'text-center'
                },
                {
                    data: 'notes',
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
                    "orderable": false,
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
                    "targets": [2, 8],
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

        // Real-time filtering for select elements
        $('.filter-select').change(function() {
            table.ajax.reload(null, false); // false = لا يعيد تعيين صفحة واحدة
        });

        // Real-time filtering for input elements with debounce
        var filterTimeout;
        $('.filter-input').on('input', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                table.ajax.reload(null, false); // false = لا يعيد تعيين صفحة واحدة
            }, 500); // 500ms delay
        });

        // Reset button click event
        $('#reset-btn').click(function() {
            $('#client_id').val('');
            $('#subscription_id').val('');
            $('#collector_id').val('');
            $('#status').val('');
            $('#min_amount').val('');
            $('#max_amount').val('');
            $('#from_date').val('');
            $('#to_date').val('');
            $('#month_filter').val('');
            $('#client_type').val('');
            $('#sort_by').val('id'); // Reset to default sort by id
            $('#sort_order').val('desc'); // Reset to default descending order
            table.ajax.reload(null, false); // false = لا يعيد تعيين صفحة واحدة
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





<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
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

<script>
    function showPayModal(url, remainingAmount, invoiceAmount, notes, currentPaidDate) {
        // console.log(notes)
        $('#payInvoiceForm').attr('action', url);
        $('#invoice_amount').val(invoiceAmount);
        // $('#paid_amount').val(remainingAmount);
        $('#remaining_amount_note').text(remainingAmount);
        if (currentPaidDate && currentPaidDate !== 'N/A') {
            $('#paid_date').val(currentPaidDate);
        } else {
            $('#paid_date').val('');
        }
        let notesValue = notes;
        if (notesValue === 'undefined' || notesValue === null || notesValue === 'null') {
            notesValue = '';
        }

        $('#notes').val(notesValue);
        $('#paid_amount').val('').attr({
            'max': remainingAmount,
            'placeholder': 'Max: ' + remainingAmount
        });
        $('#payInvoiceModal').modal('show');
    }

    $(document).on('input', '#paid_amount', function() {
        let enteredAmount = parseFloat($(this).val()) || 0;
        let remainingAmount = parseFloat($('#remaining_amount_note').text());

        if (enteredAmount > remainingAmount) {
            $(this).val(remainingAmount);
            showAmountWarning();
        }
    });

    function showAmountWarning() {
        let warning = $('#amount_warning');
        if (warning.length === 0) {
            $('<div id="amount_warning" class="text-danger mt-1 small">' +
                '<i class="bi bi-exclamation-triangle"></i> ' +
                'تم تعديل المبلغ إلى الحد الأقصى للمبلغ المتبقي' +
                '</div>').insertAfter('#paid_amount');
        } else {
            warning.show();
        }

        setTimeout(() => {
            $('#amount_warning').fadeOut();
        }, 3000);
    }

    function validateAmount() {
        let amount = $('#paid_amount').val();
        if (amount <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid amount',
                text: 'Please enter a valid amount greater than 0.',
            });
            return false;
        }
        return true;
    }
</script>

<script>
    function invoice_details(url) {
        $.get(url, function(data) {
            $('#result_info').html(data);
            $('#modaldetails').modal('show');
        });
    }

    function print_invoice(url) {
        var printWindow = window.open(url, '_blank');
        printWindow.focus();
    }
</script>
@endsection