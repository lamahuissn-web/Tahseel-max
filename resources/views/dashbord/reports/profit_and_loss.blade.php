@extends('dashbord.layouts.master')

@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('reports.profit_and_loss_report') ?? 'تقرير الربح والخسارة';
            $breadcrumbs = [
                ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
                ['label' => trans('Toolbar.reports'), 'link' => ''],
                ['label' => $title, 'link' => ''],
            ];

            PageTitle($title, $breadcrumbs);
        @endphp
    </div>
@endsection

@section('content')
    <div id="kt_app_content_container" class="app-container container-xxxl">
        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label for="month" class="form-label">الشهر</label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text">{!! form_icon('date') !!}</span>
                            <input type="month" class="form-control" name="month" id="month"
                                value="{{ date('Y-m') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="from_date" class="form-label">من تاريخ</label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text">{!! form_icon('date') !!}</span>
                            <input type="date" class="form-control" name="from_date" id="from_date"
                                value="">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="to_date" class="form-label">إلى تاريخ</label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text">{!! form_icon('date') !!}</span>
                            <input type="date" class="form-control" name="to_date" id="to_date"
                                value="">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" id="search_btn">
                            <i class="bi bi-search"></i> بحث
                        </button>
                    </div>
                </div>

                <div class="row mb-4" id="totals_section" style="display: none;">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="bg-success bg-opacity-10 rounded p-4 text-center">
                                    <h5 class="text-success mb-2">{{ trans('reports.total_revenue') ?? 'إجمالي الإيرادات' }}</h5>
                                    <h3 class="text-success fw-bold mb-0" id="total_revenue">0.00</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-danger bg-opacity-10 rounded p-4 text-center">
                                    <h5 class="text-danger mb-2">{{ trans('reports.total_expenses') ?? 'إجمالي المصروفات' }}</h5>
                                    <h3 class="text-danger fw-bold mb-0" id="total_expenses">0.00</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-info bg-opacity-10 rounded p-4 text-center" id="profit_section">
                                    <h5 class="text-info mb-2">{{ trans('reports.profit') ?? 'الربح' }}</h5>
                                    <h3 class="text-info fw-bold mb-0" id="profit_amount">0.00</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-warning bg-opacity-10 rounded p-4 text-center" id="loss_section" style="display: none;">
                                    <h5 class="text-warning mb-2">{{ trans('reports.loss') ?? 'الخسارة' }}</h5>
                                    <h3 class="text-warning fw-bold mb-0" id="loss_amount">0.00</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm" style="border-top: 3px solid #28a745;">
            <div class="card-header">
                <h3 class="card-title">{{ trans('reports.profit_and_loss_report') ?? 'تقرير الربح والخسارة' }}</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="profit_loss_table">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">التاريخ</th>
                                <th class="text-center">{{ trans('reports.revenue') ?? 'الإيرادات' }}</th>
                                <th class="text-center">{{ trans('reports.revenue_count') ?? 'عدد الإيرادات' }}</th>
                                <th class="text-center">{{ trans('reports.expenses') ?? 'المصروفات' }}</th>
                                <th class="text-center">{{ trans('reports.expenses_count') ?? 'عدد المصروفات' }}</th>
                                <th class="text-center">{{ trans('reports.net') ?? 'صافي الربح/الخسارة' }}</th>
                            </tr>
                        </thead>
                        <tbody id="report_tbody">
                            <tr>
                                <td colspan="7" class="text-center">اضغط على زر البحث لعرض التقرير</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#search_btn').on('click', function() {
                loadProfitLossReport();
            });

            // Load report on page load with current month
            loadProfitLossReport();

            function loadProfitLossReport() {
                const month = $('#month').val();
                const fromDate = $('#from_date').val();
                const toDate = $('#to_date').val();

                $.ajax({
                    url: "{{ route('admin.reports.profit_and_loss_data') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        month: month,
                        from_date: fromDate,
                        to_date: toDate
                    },
                    success: function(response) {
                        displayReport(response);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        alert('حدث خطأ أثناء تحميل التقرير');
                    }
                });
            }

            function displayReport(data) {
                const tbody = $('#report_tbody');
                tbody.empty();

                if (data.daily_data && data.daily_data.length > 0) {
                    data.daily_data.forEach(function(item, index) {
                        const netAmount = parseFloat(item.profit || 0);
                        const netClass = netAmount >= 0 ? 'text-success' : 'text-danger';
                        const netText = netAmount >= 0 ? 'ربح' : 'خسارة';
                        
                        const row = `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td class="text-center fw-bold">${item.date}</td>
                                <td class="text-center fw-bold text-success">${parseFloat(item.revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${item.revenue_count || 0}</td>
                                <td class="text-center fw-bold text-danger">${parseFloat(item.expenses || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${item.expenses_count || 0}</td>
                                <td class="text-center fw-bold ${netClass}">${parseFloat(netAmount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} (${netText})</td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="7" class="text-center">لا توجد بيانات للفترة المحددة</td></tr>');
                }

                // Update totals
                if (data.totals) {
                    const profit = parseFloat(data.totals.profit || 0);
                    
                    $('#total_revenue').text(parseFloat(data.totals.total_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#total_expenses').text(parseFloat(data.totals.total_expenses || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    
                    if (profit >= 0) {
                        $('#profit_amount').text(parseFloat(profit).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#profit_section').removeClass('bg-warning').addClass('bg-info');
                        $('#profit_section').find('h5').removeClass('text-warning').addClass('text-info');
                        $('#profit_section').find('h3').removeClass('text-warning').addClass('text-info');
                        $('#profit_section').show();
                        $('#loss_section').hide();
                    } else {
                        $('#loss_amount').text(parseFloat(data.totals.loss || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#loss_section').show();
                        $('#profit_section').hide();
                    }
                    
                    $('#totals_section').show();
                }
            }
        });
    </script>
@endsection

