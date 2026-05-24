@extends('dashbord.layouts.master')

@section('toolbar')
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        @php
            $title = trans('reports.expenses_by_bands_and_months') ?? 'تقرير المصروفات بالبنود والشهور';
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
                    <div class="col-md-4">
                        <label for="year" class="form-label">السنة</label>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text">{!! form_icon('date') !!}</span>
                            <input type="number" class="form-control" name="year" id="year"
                                value="{{ date('Y') }}" min="2000" max="2100">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" id="search_btn">
                            <i class="bi bi-search"></i> بحث
                        </button>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="bg-info bg-opacity-10 rounded p-3 d-inline-block w-100 text-center">
                            <span class="text-info fw-bold">{{ trans('reports.grand_total') ?? 'الإجمالي العام' }}: </span>
                            <span class="text-info fw-bold" id="grand_total">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm" style="border-top: 3px solid #28a745;">
            <div class="card-header">
                <h3 class="card-title">{{ trans('reports.expenses_by_bands_and_months') ?? 'تقرير المصروفات بالبنود والشهور' }}</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-striped table-bordered table-hover" id="expenses_table">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center sticky-column" style="position: sticky; left: 0; background: #212529; z-index: 10;">{{ trans('reports.band') ?? 'البند' }}</th>
                                <th class="text-center">يناير</th>
                                <th class="text-center">فبراير</th>
                                <th class="text-center">مارس</th>
                                <th class="text-center">أبريل</th>
                                <th class="text-center">مايو</th>
                                <th class="text-center">يونيو</th>
                                <th class="text-center">يوليو</th>
                                <th class="text-center">أغسطس</th>
                                <th class="text-center">سبتمبر</th>
                                <th class="text-center">أكتوبر</th>
                                <th class="text-center">نوفمبر</th>
                                <th class="text-center">ديسمبر</th>
                                <th class="text-center bg-primary text-white">{{ trans('reports.total') ?? 'الإجمالي' }}</th>
                            </tr>
                        </thead>
                        <tbody id="report_tbody">
                            <tr>
                                <td colspan="14" class="text-center">اختر السنة واضغط على زر البحث</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-info">
                            <tr id="totals_row" style="display: none;">
                                <th class="text-center sticky-column bg-info" style="position: sticky; left: 0; z-index: 10;">{{ trans('reports.total') ?? 'الإجمالي' }}</th>
                                <td class="text-center fw-bold" id="month_1_total">0.00</td>
                                <td class="text-center fw-bold" id="month_2_total">0.00</td>
                                <td class="text-center fw-bold" id="month_3_total">0.00</td>
                                <td class="text-center fw-bold" id="month_4_total">0.00</td>
                                <td class="text-center fw-bold" id="month_5_total">0.00</td>
                                <td class="text-center fw-bold" id="month_6_total">0.00</td>
                                <td class="text-center fw-bold" id="month_7_total">0.00</td>
                                <td class="text-center fw-bold" id="month_8_total">0.00</td>
                                <td class="text-center fw-bold" id="month_9_total">0.00</td>
                                <td class="text-center fw-bold" id="month_10_total">0.00</td>
                                <td class="text-center fw-bold" id="month_11_total">0.00</td>
                                <td class="text-center fw-bold" id="month_12_total">0.00</td>
                                <td class="text-center fw-bold bg-primary text-white" id="grand_total_footer">0.00</td>
                            </tr>
                        </tfoot>
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
                loadExpensesReport();
            });

            // Load report on page load with current year
            loadExpensesReport();

            function loadExpensesReport() {
                const year = $('#year').val();
                
                if (!year || year < 2000 || year > 2100) {
                    alert('الرجاء اختيار سنة صحيحة');
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.reports.expenses_by_bands_and_months_data') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        year: year
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

                if (data.bands_data && data.bands_data.length > 0) {
                    data.bands_data.forEach(function(band) {
                        const row = `
                            <tr>
                                <td class="fw-bold sticky-column bg-light" style="position: sticky; left: 0; z-index: 5;">${band.band_title}</td>
                                <td class="text-center">${parseFloat(band.months[1] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${parseFloat(band.months[2] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${parseFloat(band.months[3] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${parseFloat(band.months[4] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${parseFloat(band.months[5] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${parseFloat(band.months[6] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${parseFloat(band.months[7] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${parseFloat(band.months[8] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${parseFloat(band.months[9] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${parseFloat(band.months[10] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${parseFloat(band.months[11] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center">${parseFloat(band.months[12] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-center fw-bold bg-light">${parseFloat(band.total || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="14" class="text-center">لا توجد بيانات للسنة المحددة</td></tr>');
                }

                // Update totals
                if (data.month_totals) {
                    for (let month = 1; month <= 12; month++) {
                        $(`#month_${month}_total`).text(parseFloat(data.month_totals[month] || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    }
                    $('#totals_row').show();
                }

                // Update grand total
                if (data.grand_total !== undefined) {
                    $('#grand_total').text(parseFloat(data.grand_total || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#grand_total_footer').text(parseFloat(data.grand_total || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                }
            }
        });
    </script>
    <style>
        .sticky-column {
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        #expenses_table {
            font-size: 14px;
        }
        #expenses_table th,
        #expenses_table td {
            white-space: nowrap;
            padding: 10px 8px;
        }
    </style>
@endsection

