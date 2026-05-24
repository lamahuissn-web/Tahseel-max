@extends('dashbord.layouts.mobile_master')

@section('content')
<div class="row g-2 justify-content-center">
    <!-- Header with Back Button and Title -->
    <div class="col-12 mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <h1 class="fw-bolder fs-2tx text-dark mb-0 d-flex align-items-center">
                <i class="bi bi-clock-history fs-2tx text-primary me-3"></i>
                مراجعة الفواتير
            </h1>
            <a href="{{ route('admin.mobile_view') }}" class="btn btn-icon btn-sm btn-primary w-40px h-40px rounded-circle">
                <i class="bi bi-arrow-left fs-2 fw-bold"></i>
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="col-12 mb-4 sticky-top bg-body py-3 shadow-sm rounded-bottom z-index-3">
        <div class="d-flex gap-3 align-items-center w-100 flex-wrap">
            <div class="position-relative flex-grow-1 w-100">
                <span class="position-absolute top-50 translate-middle-y ms-4 start-0 text-gray-500">
                    <i class="bi bi-search fs-3"></i>
                </span>
                <input type="text" id="invoice_search" class="form-control form-control-solid ps-12 py-3 rounded-pill fs-5" placeholder="بحث برقم الفاتورة أو اسم العميل...">
            </div>
            <div class="d-flex gap-2 w-100">
                <div class="flex-grow-1">
                    <label for="date_from" class="form-label fw-bold">من تاريخ</label>
                    <input type="date" id="date_from" class="form-control form-control-solid rounded-pill fs-6">
                </div>
                <div class="flex-grow-1">
                    <label for="date_to" class="form-label fw-bold">إلى تاريخ</label>
                    <input type="date" id="date_to" class="form-control form-control-solid rounded-pill fs-6">
                </div>
            </div>
            <div id="search_loader" class="d-none">
                <div class="spinner-border text-primary spinner-border-sm" role="status">
                    <span class="visually-hidden">جاري البحث...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Container -->
    <div id="invoices_container_wrapper" class="row g-4 w-100 mx-0">
        @include('dashbord.mobile_view.partials.invoices_list')
    </div>
</div>

<!-- Pay Modal -->
<div class="modal fade" id="pay_modal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">دفع الفاتورة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pay_form" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="invoice_amount">{{ trans('invoices.amount') ?? 'المبلغ' }}</label>
                        <input type="number" step="0.01" class="form-control" name="invoice_amount" id="invoice_amount" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">المبلغ الإجمالي</label>
                        <input type="number" step="0.01" class="form-control" name="paid_amount" id="paid_amount" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">تاريخ الدفع</label>
                        <input type="date" class="form-control" name="paid_date" id="paid_date" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">ملاحظات</label>
                        <textarea class="form-control" name="notes" id="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تأكيد الدفع</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    let typingTimer;
    const doneTypingInterval = 500;
    const $input = $('#invoice_search');
    const $dateFrom = $('#date_from');
    const $dateTo = $('#date_to');
    const $searchLoader = $('#search_loader');
    const $container = $('#invoices_container_wrapper');
    let page = 1;
    let loading = false;
    let hasMorePages = true;

    $input.on('keyup', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(doneTyping, doneTypingInterval);
    });

    $input.on('keydown', function() {
        clearTimeout(typingTimer);
    });

    $dateFrom.on('change', function() {
        page = 1;
        hasMorePages = true;
        fetch_data(page, $input.val(), true);
    });

    $dateTo.on('change', function() {
        page = 1;
        hasMorePages = true;
        fetch_data(page, $input.val(), true);
    });

    function doneTyping() {
        page = 1;
        hasMorePages = true;
        let query = $input.val();
        fetch_data(page, query, true);
    }

    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100 && !loading && hasMorePages) {
            page++;
            let query = $input.val();
            fetch_data(page, query, false);
        }
    });

    function fetch_data(pageNumber, query, reset) {
        if (loading) return;
        loading = true;

        $.ajax({
            url: "{{ route('admin.mobile_invoices') }}",
            data: {
                page: pageNumber,
                search: query,
                review: "{{ request('review') }}",
                date_from: $dateFrom.val(),
                date_to: $dateTo.val()
            },
            beforeSend: function() {
                $searchLoader.removeClass('d-none');
                if (reset) {
                    $container.css('opacity', '0.5');
                } else {
                    $('#loading_spinner').remove();
                    $container.append('<div id="loading_spinner" class="col-12 text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></div>');
                }
            },
            success: function(data) {
                $('#loading_spinner').remove();
                $searchLoader.addClass('d-none');

                // Simple check: if returned data has invoice cards
                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = data;
                let invoiceCards = tempDiv.querySelectorAll('.card');

                console.log('Page:', pageNumber, 'Cards found:', invoiceCards.length);

                if (invoiceCards.length === 0) {
                    hasMorePages = false;
                    if (reset) {
                        $container.html('<div class="col-12 text-center py-5"><div class="text-muted fs-5">لا توجد بيانات</div></div>');
                    }
                } else {
                    if (reset) {
                        $container.html(data);
                    } else {
                        $container.append(data);
                    }
                }
                $container.css('opacity', '1');
                loading = false;
            },
            error: function(xhr, status, error) {
                $('#loading_spinner').remove();
                $searchLoader.addClass('d-none');
                $container.css('opacity', '1');
                console.error('Error:', error);
                toastr.error('حدث خطأ أثناء تحميل البيانات');
                loading = false;
            }
        });
    }

    function showPayModal(url, remaining, total, notes, paid_date) {
        $('#pay_form').attr('action', url);
        $('#invoice_amount').val(remaining);
        $('#paid_amount').val(remaining); // Default to full remaining amount
        $('#notes').val(notes);
        if (paid_date) {
            $('#paid_date').val(paid_date.split(' ')[0]);
        }
        $('#pay_modal').modal('show');
    }
</script>
@endsection
