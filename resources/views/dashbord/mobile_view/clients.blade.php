@extends('dashbord.layouts.mobile_master')

@section('content')
<div class="row g-2 justify-content-center">
    <!-- Header with Back Button and Title -->
    <div class="col-12 mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column gap-1">
                <h1 class="fw-bolder fs-2tx text-dark mb-0 d-flex align-items-center">
                    <i class="bi bi-people fs-2tx text-primary me-3"></i>
                    {{  'العملاء' }}
                </h1>
                <div class="fs-6 text-gray-600 fw-semibold" id="mobileClientsCount">
                    {{ $totalCount ?? 0 }} عميل
                </div>
            </div>
            <a href="{{ route('admin.mobile_view') }}" class="btn btn-icon btn-sm btn-light-primary w-40px h-40px rounded-circle">
                <i class="bi bi-arrow-left fs-2 fw-bold"></i>
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="col-12 mb-4 sticky-top bg-body py-3 shadow-sm rounded-bottom z-index-3">
        <div class="d-flex gap-3 align-items-center w-100">
            <div class="position-relative flex-grow-1 w-100">
                <span class="position-absolute top-50 translate-middle-y ms-4 start-0 text-gray-500">
                    <i class="bi bi-search fs-3"></i>
                </span>
                <input type="text" id="client_search" class="form-control form-control-solid ps-12 py-3 rounded-pill fs-5" placeholder="بحث بالاسم أو الهاتف...">
            </div>
            <button class="btn btn-icon btn-primary rounded-3 w-50px h-50px shadow-sm">
                <i class="bi bi-sliders fs-2"></i>
            </button>
        </div>
    </div>

    <!-- Results Container -->
    <div id="clients_container_wrapper" class="row g-4 w-100 mx-0">
        @include('dashbord.mobile_view.partials.clients_list')
    </div>
</div>
@endsection

@section('js')
<script>
    let typingTimer;
    const doneTypingInterval = 500;
    const $input = $('#client_search');
    const $container = $('#clients_container_wrapper');
    const sasStatusUrl = "{{ route('admin.sas4.online_status') }}";
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
            url: "{{ route('admin.mobile_clients') }}",
            data: {
                page: pageNumber,
                search: query
            },
            beforeSend: function() {
                if (reset) {
                    $container.css('opacity', '0.5');
                } else {
                    $('#loading_spinner').remove();
                    $container.append('<div id="loading_spinner" class="col-12 text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></div>');
                }
            },
            success: function(response) {
                $('#loading_spinner').remove();

                let data = response.html;
                let total = response.total ?? 0;
                $('#mobileClientsCount').text(total + ' عميل');

                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = data;
                let clientCards = tempDiv.querySelectorAll('.card');

                console.log('Page:', pageNumber, 'Cards found:', clientCards.length);

                if (clientCards.length === 0) {
                    hasMorePages = false;
                    if (reset) {
                        $container.html('<div class="col-12 text-center py-5"><div class="d-flex flex-column align-items-center justify-content-center"><i class="bi bi-clipboard-x fs-5x text-muted mb-3"></i><div class="text-muted fs-4 fw-bold">لا توجد بيانات</div></div></div>');
                    }
                } else {
                    if (reset) {
                        $container.html(data);
                    } else {
                        $container.append(data);
                    }
                    loadMobileSasStatuses();
                }
                $container.css('opacity', '1');
                loading = false;
            },
            error: function(xhr, status, error) {
                $('#loading_spinner').remove();
                $container.css('opacity', '1');
                console.error('Error:', error);
                toastr.error('حدث خطأ أثناء تحميل البيانات');
                loading = false;
            }
        });
    }

    function updateMobileSasBadge($badge, cssClass, label) {
        $badge
            .removeClass('badge-light-warning badge-light-success badge-light-secondary badge-light-danger')
            .addClass(cssClass)
            .text(label)
            .attr('data-loaded', '1');
    }

    function loadMobileSasStatuses() {
        const $badges = $('.mobile-sas-indicator').not('[data-loaded="1"]');
        if (!$badges.length) return;

        let usernames = [];
        let badgeMap = {};

        $badges.each(function() {
            const $badge = $(this);
            const username = $badge.data('username');
            if (username) {
                if (!badgeMap[username]) {
                    usernames.push(username);
                    badgeMap[username] = [];
                }
                badgeMap[username].push($badge);
            }
        });

        if (!usernames.length) return;

        $.ajax({
            url: sasStatusUrl,
            type: 'POST',
            data: { usernames: usernames },
            dataType: 'json',
            success: function(response) {
                usernames.forEach(function(username) {
                    const badges = badgeMap[username] || [];
                    const info = response[username];
                    badges.forEach(function($badge) {
                        if (!info) {
                            updateMobileSasBadge($badge, 'badge-light-secondary', 'غير معروف');
                        } else if (String(info.enabled) === '0') {
                            updateMobileSasBadge($badge, 'badge-light-danger', 'موقوف');
                        } else if (String(info.online) === '1') {
                            updateMobileSasBadge($badge, 'badge-light-success', 'متصل');
                        } else {
                            updateMobileSasBadge($badge, 'badge-light-secondary', 'غير متصل');
                        }
                    });
                });
            },
            error: function() {
                $badges.each(function() {
                    updateMobileSasBadge($(this), 'badge-light-secondary', 'غير معروف');
                });
            }
        });
    }

    loadMobileSasStatuses();
</script>
@endsection